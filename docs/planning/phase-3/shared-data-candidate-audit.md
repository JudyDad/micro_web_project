# Whole-table shared-data candidate audit

Read-only frozen SQL audit, 17 October 2026. **Candidates only; no rows, schemas or approved mappings changed.** User authorized whole identical tables only. Matching subsets in differing tables are not candidates.

All five ZIP and SQL SHA-256 digests matched backup-audit.json. Every parsed table count matched its frozen inventory baseline. No raw rows, values, row IDs or individual-row hashes are emitted.

Method: Exact decoded SQL-string/binary payload and numeric literal spelling; ordered source column/storage/nullability contract; multiset tuple hashes retain duplicates; schema syntax-only charset inference; no data normalization or SQL execution. Cryptographic digests are comparison accelerators, not permission to merge on ID alone. SHA-256 collision resistance is assumed. Original numeric token spelling is strict; semantically equal numbers with different dump spellings may be conservative false negatives. Table order and row order may differ; ordered column contracts and duplicate multiplicities remain significant. Source defaults/autoincrement counters remain provenance and future-write decisions, not payload equality.

Verified **616 table instances / 127 names / 3947 columns / 1721699 tuple occurrences**.

## Nonempty whole-table equality groups

| Table | Equal cities | Rows each | Duplicate occurrences | Declared parent closure passes | Unmatched parents |
|---|---|---:|---:|---|---|
| aganswer | Aleppo, Damascus, Homs, Latakia, AlWadi | 53 | 212 | yes | none |
| age | Aleppo, Damascus, Homs, Latakia, AlWadi | 6 | 24 | yes | none |
| agquestion | Aleppo, Damascus, Homs, Latakia, AlWadi | 14 | 56 | yes | none |
| aid | Aleppo, Latakia, AlWadi | 20 | 40 | yes | none |
| aidperiod | Aleppo, Damascus, Homs, Latakia, AlWadi | 7 | 28 | yes | none |
| asset | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 16 | yes | none |
| assetbenefits | Aleppo, Damascus, Homs, Latakia, AlWadi | 3 | 12 | yes | none |
| assettype | Aleppo, Damascus, Homs, Latakia, AlWadi | 7 | 28 | yes | none |
| backgrounds | Aleppo, Damascus, Homs, Latakia, AlWadi | 1 | 4 | yes | none |
| career | Homs, AlWadi | 289 | 289 | yes | none |
| category | Aleppo, Damascus, Homs, Latakia, AlWadi | 6 | 24 | yes | none |
| checklist_opinion | Aleppo, Damascus, Homs, Latakia, AlWadi | 3 | 12 | yes | none |
| course | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 16 | yes | none |
| databases | Damascus, Homs, AlWadi | 4 | 8 | yes | none |
| donor | Aleppo, Latakia | 15 | 15 | yes | none |
| educationlevel | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 16 | yes | none |
| educationtype | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 20 | yes | none |
| fundtype | Aleppo, Damascus, Homs, Latakia, AlWadi | 2 | 8 | yes | none |
| goal | Aleppo, Damascus, Homs, Latakia, AlWadi | 15 | 60 | yes | none |
| illness | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 20 | yes | none |
| imagetype | Aleppo, Damascus, Homs, Latakia, AlWadi | 12 | 48 | yes | none |
| incomesource | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 16 | yes | none |
| language | Aleppo, Damascus, Homs, Latakia, AlWadi | 11 | 44 | yes | none |
| level | Aleppo, Damascus, Latakia | 4 | 8 | yes | none |
| level | Homs, AlWadi | 4 | 4 | yes | none |
| loss | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 20 | yes | none |
| manualfile | Aleppo, Latakia | 1 | 1 | yes | none |
| maritalstatus | Aleppo, Damascus, Homs, Latakia, AlWadi | 7 | 28 | yes | none |
| meanswer | Aleppo, Damascus, Homs, Latakia, AlWadi | 104 | 416 | yes | none |
| medicalneeds | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 20 | yes | none |
| meprofitmarks | Aleppo, Latakia | 5 | 5 | yes | none |
| meprofitmarks | Damascus, Homs, AlWadi | 5 | 10 | yes | none |
| mequestion | Aleppo, Damascus, Homs, Latakia, AlWadi | 42 | 168 | yes | none |
| meresultranges | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 16 | yes | none |
| microprojectsubtype | Aleppo, Damascus, Homs, Latakia, AlWadi | 17 | 68 | yes | none |
| microprojecttype | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 20 | yes | none |
| militarystate | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 20 | yes | none |
| parish | Aleppo, Damascus, Homs, Latakia, AlWadi | 11 | 44 | yes | none |
| personstate | Aleppo, Damascus, Homs, Latakia, AlWadi | 8 | 32 | yes | none |
| powersource | Aleppo, Damascus, Homs, Latakia, AlWadi | 7 | 28 | yes | none |
| profession_option | Aleppo, Damascus, Homs, Latakia, AlWadi | 44 | 176 | yes | none |
| profession_question | Aleppo, Damascus, Homs, Latakia, AlWadi | 22 | 88 | yes | none |
| profession_section | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 20 | yes | none |
| rehabanswer | Aleppo, Damascus, Homs, Latakia, AlWadi | 56 | 224 | yes | none |
| rehabquestion | Aleppo, Damascus, Homs, Latakia, AlWadi | 24 | 96 | yes | none |
| relation | Aleppo, Damascus, Homs, Latakia, AlWadi | 27 | 108 | yes | none |
| resultstable | Homs, AlWadi | 717 | 717 | yes | none |
| risk | Aleppo, Damascus, Homs, Latakia, AlWadi | 3 | 12 | yes | none |
| role | Aleppo, Damascus, Homs, Latakia, AlWadi | 8 | 32 | yes | none |
| score | Aleppo, Damascus, Homs, Latakia, AlWadi | 10 | 40 | yes | none |
| scorevalue | Aleppo, Damascus, Homs, Latakia, AlWadi | 30 | 120 | yes | none |
| specialcasetype | Aleppo, Damascus, Homs, Latakia, AlWadi | 2 | 8 | yes | none |
| state | Aleppo, Damascus, Homs, Latakia, AlWadi | 11 | 44 | yes | none |
| statereason | Damascus, Latakia | 25 | 25 | yes | none |
| statereason | Homs, AlWadi | 25 | 25 | yes | none |
| subcategory | Aleppo, Damascus, Homs, Latakia, AlWadi | 17 | 68 | yes | none |
| task | Aleppo, Damascus, Homs, Latakia, AlWadi | 39 | 156 | yes | none |
| version | Aleppo, Damascus, Homs, Latakia, AlWadi | 1 | 4 | yes | none |
| visitanswers | Aleppo, Damascus, Homs, Latakia, AlWadi | 43 | 172 | yes | none |
| visitquestions | Aleppo, Damascus, Homs, Latakia, AlWadi | 20 | 80 | yes | none |
| worktype | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 20 | yes | none |

Declared-parent closure is conservative and requires full exact parent equality throughout each group. It does not certify undeclared application relationships, mutable-write semantics, credentials, tenant visibility or permission equivalence. Historical actors and tasks require separate membership/authorization handling. These groups never authorize merging projects, people or accounts by coincidentally matching IDs.

## Identical empty-table groups

| Table | Equal cities |
|---|---|
| exefile | Damascus, Homs, Latakia, AlWadi |
| file | Damascus, Homs, Latakia, AlWadi |
| manualfile | Damascus, Homs, AlWadi |
| microproject_ag_answer | Aleppo, Damascus, Homs, Latakia, AlWadi |
| microproject_rehab | Damascus, Homs, Latakia, AlWadi |
| microproject_rehab_answer | Damascus, Homs, Latakia, AlWadi |
| profession_meeting | Damascus, AlWadi |
| profession_meeting_answer | Damascus, AlWadi |
| profession_meeting_attendee | Damascus, AlWadi |
| profession_meeting_brand | Damascus, AlWadi |
| profession_meeting_challenge | Damascus, AlWadi |
| profession_meeting_competition | Damascus, AlWadi |
| profession_meeting_equipment | Damascus, AlWadi |
| profession_meeting_minute | Damascus, AlWadi |
| profession_meeting_option_answer | Damascus, AlWadi |
| profession_meeting_participant | Damascus, AlWadi |
| profession_meeting_season | Damascus, AlWadi |
| purchasing | Aleppo, Damascus, Homs, Latakia, AlWadi |
| purchasingcategory | Aleppo, Damascus, Homs, Latakia, AlWadi |
| rehabvisit | Damascus, Homs, Latakia, AlWadi |

Empty equality is not evidence of shared future business scope; there are no existing rows to consolidate.

## Requested category, subcategory and task definitions

| Table | City counts | Whole equal groups | Max same-ID conflicts between city pairs |
|---|---|---:|---:|
| category | Aleppo: 6, Damascus: 6, Homs: 6, Latakia: 6, AlWadi: 6 | 1 | 0 |
| subcategory | Aleppo: 17, Damascus: 17, Homs: 17, Latakia: 17, AlWadi: 17 | 1 | 0 |
| task | Aleppo: 39, Damascus: 39, Homs: 39, Latakia: 39, AlWadi: 39 | 1 | 0 |
| task_microproject | Aleppo: 48541, Damascus: 25788, Homs: 46397, Latakia: 12137, AlWadi: 10790 | 0 | 37410 |
| task_user | Aleppo: 76, Damascus: 73, Homs: 121, Latakia: 144, AlWadi: 132 | 0 | 62 |

## All-table disposition coverage

| Table | Counts by city | Nonempty equal groups | Empty equal groups | Max same-ID conflicts |
|---|---|---:|---:|---:|
| aganswer | Aleppo: 53, Damascus: 53, Homs: 53, Latakia: 53, AlWadi: 53 | 1 | 0 | 0 |
| age | Aleppo: 6, Damascus: 6, Homs: 6, Latakia: 6, AlWadi: 6 | 1 | 0 | 0 |
| agquestion | Aleppo: 14, Damascus: 14, Homs: 14, Latakia: 14, AlWadi: 14 | 1 | 0 | 0 |
| aid | Aleppo: 20, Damascus: 22, Homs: 21, Latakia: 20, AlWadi: 20 | 1 | 0 | 1 |
| aidperiod | Aleppo: 7, Damascus: 7, Homs: 7, Latakia: 7, AlWadi: 7 | 1 | 0 | 0 |
| aidsource | Aleppo: 26, Damascus: 34, Homs: 35, Latakia: 27, AlWadi: 27 | 0 | 0 | 8 |
| asset | Aleppo: 4, Damascus: 4, Homs: 4, Latakia: 4, AlWadi: 4 | 1 | 0 | 0 |
| assetbenefits | Aleppo: 3, Damascus: 3, Homs: 3, Latakia: 3, AlWadi: 3 | 1 | 0 | 0 |
| assettype | Aleppo: 7, Damascus: 7, Homs: 7, Latakia: 7, AlWadi: 7 | 1 | 0 | 0 |
| backgrounds | Aleppo: 1, Damascus: 1, Homs: 1, Latakia: 1, AlWadi: 1 | 1 | 0 | 0 |
| career | Aleppo: 289, Damascus: 283, Homs: 289, Latakia: 289, AlWadi: 289 | 1 | 0 | 11 |
| category | Aleppo: 6, Damascus: 6, Homs: 6, Latakia: 6, AlWadi: 6 | 1 | 0 | 0 |
| checklist | Aleppo: 3879, Damascus: 2230, Homs: 2504, Latakia: 553, AlWadi: 427 | 0 | 0 | 2504 |
| checklist_member | Aleppo: 49, Damascus: 42, Homs: 48, Latakia: 27, AlWadi: 47 | 0 | 0 | 35 |
| checklist_opinion | Aleppo: 3, Damascus: 3, Homs: 3, Latakia: 3, AlWadi: 3 | 1 | 0 | 0 |
| checklist_position | Aleppo: 25, Damascus: 27, Homs: 27, Latakia: 17, AlWadi: 28 | 0 | 0 | 16 |
| course | Aleppo: 4, Damascus: 4, Homs: 4, Latakia: 4, AlWadi: 4 | 1 | 0 | 0 |
| databases | Aleppo: 6, Damascus: 4, Homs: 4, Latakia: 6, AlWadi: 4 | 1 | 0 | 1 |
| dept | Damascus: 2043 | 0 | 0 | 0 |
| donor | Aleppo: 15, Damascus: 11, Homs: 12, Latakia: 15, AlWadi: 10 | 1 | 0 | 3 |
| donorgroup | Aleppo: 43, Damascus: 23, Homs: 57, Latakia: 40, AlWadi: 12 | 0 | 0 | 41 |
| education | Aleppo: 96, Damascus: 174, Homs: 181, Latakia: 104, AlWadi: 178 | 0 | 0 | 81 |
| educationlevel | Aleppo: 4, Damascus: 4, Homs: 4, Latakia: 4, AlWadi: 4 | 1 | 0 | 0 |
| educationtype | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 1 | 0 | 0 |
| exefile | Aleppo: 145, Damascus: 0, Homs: 0, Latakia: 0, AlWadi: 0 | 0 | 1 | 0 |
| family | Aleppo: 3855, Damascus: 2340, Homs: 2914, Latakia: 590, AlWadi: 612 | 0 | 0 | 2784 |
| file | Aleppo: 32, Damascus: 0, Homs: 0, Latakia: 0, AlWadi: 0 | 0 | 1 | 0 |
| fundtype | Aleppo: 2, Damascus: 2, Homs: 2, Latakia: 2, AlWadi: 2 | 1 | 0 | 0 |
| goal | Aleppo: 15, Damascus: 15, Homs: 15, Latakia: 15, AlWadi: 15 | 1 | 0 | 0 |
| illness | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 1 | 0 | 0 |
| image | Aleppo: 9511, Damascus: 9960, Homs: 19341, Latakia: 6067, AlWadi: 3486 | 0 | 0 | 9282 |
| imagetype | Aleppo: 12, Damascus: 12, Homs: 12, Latakia: 12, AlWadi: 12 | 1 | 0 | 0 |
| incomesource | Aleppo: 4, Damascus: 4, Homs: 4, Latakia: 4, AlWadi: 4 | 1 | 0 | 0 |
| language | Aleppo: 11, Damascus: 11, Homs: 11, Latakia: 11, AlWadi: 11 | 1 | 0 | 0 |
| level | Aleppo: 4, Damascus: 4, Homs: 4, Latakia: 4, AlWadi: 4 | 2 | 0 | 0 |
| loan | Aleppo: 989, Damascus: 544, Homs: 1160, Latakia: 199, AlWadi: 161 | 0 | 0 | 950 |
| log | Aleppo: 106082, Damascus: 124885, Homs: 291976, Latakia: 48197, AlWadi: 50990 | 0 | 0 | 106047 |
| loss | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 1 | 0 | 0 |
| manualfile | Aleppo: 1, Damascus: 0, Homs: 0, Latakia: 1, AlWadi: 0 | 1 | 1 | 0 |
| maritalstatus | Aleppo: 7, Damascus: 7, Homs: 7, Latakia: 7, AlWadi: 7 | 1 | 0 | 0 |
| material | Aleppo: 2988, Damascus: 2159, Homs: 2861, Latakia: 4280, AlWadi: 838 | 0 | 0 | 2948 |
| meanswer | Aleppo: 104, Damascus: 104, Homs: 104, Latakia: 104, AlWadi: 104 | 1 | 0 | 0 |
| medicalneeds | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 1 | 0 | 0 |
| meprofitmarks | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 2 | 0 | 4 |
| mequestion | Aleppo: 42, Damascus: 42, Homs: 42, Latakia: 42, AlWadi: 42 | 1 | 0 | 0 |
| meresultranges | Aleppo: 4, Damascus: 4, Homs: 4, Latakia: 4, AlWadi: 4 | 1 | 0 | 0 |
| mevisit | Aleppo: 2971, Damascus: 1468, Homs: 2940, Latakia: 28, AlWadi: 0 | 0 | 0 | 2606 |
| mevisit_answer | Aleppo: 60378, Damascus: 31458, Homs: 68610, Latakia: 666, AlWadi: 0 | 0 | 0 | 39779 |
| mevisit_user | Aleppo: 4247, Damascus: 2319, Homs: 3165, Latakia: 52, AlWadi: 0 | 0 | 0 | 1850 |
| microproject | Aleppo: 3932, Damascus: 2363, Homs: 2974, Latakia: 593, AlWadi: 650 | 0 | 0 | 2974 |
| microproject_ag_answer | Aleppo: 0, Damascus: 0, Homs: 0, Latakia: 0, AlWadi: 0 | 0 | 1 | 0 |
| microproject_powersource | Aleppo: 2079, Damascus: 78, Homs: 160, Latakia: 347, AlWadi: 121 | 0 | 0 | 347 |
| microproject_rehab | Aleppo: 2, Damascus: 0, Homs: 0, Latakia: 0, AlWadi: 0 | 0 | 1 | 0 |
| microproject_rehab_answer | Aleppo: 48, Damascus: 0, Homs: 0, Latakia: 0, AlWadi: 0 | 0 | 1 | 0 |
| microproject_risk | Aleppo: 23, Damascus: 60, Homs: 206, Latakia: 49, AlWadi: 33 | 0 | 0 | 20 |
| microproject_score | Aleppo: 35881, Damascus: 20690, Homs: 20930, Latakia: 2060, AlWadi: 3810 | 0 | 0 | 8129 |
| microproject_state | Aleppo: 9606, Damascus: 6148, Homs: 7763, Latakia: 2112, AlWadi: 1522 | 0 | 0 | 5798 |
| microprojectsubtype | Aleppo: 17, Damascus: 17, Homs: 17, Latakia: 17, AlWadi: 17 | 1 | 0 | 0 |
| microprojecttype | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 1 | 0 | 0 |
| militarystate | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 1 | 0 | 0 |
| office_monitoring | Aleppo: 1178, Damascus: 501, Homs: 1431, Latakia: 68, AlWadi: 33 | 0 | 0 | 1176 |
| parish | Aleppo: 11, Damascus: 11, Homs: 11, Latakia: 11, AlWadi: 11 | 1 | 0 | 0 |
| payment | Aleppo: 20656, Damascus: 8216, Homs: 20612, Latakia: 1650, AlWadi: 1087 | 0 | 0 | 19392 |
| person | Aleppo: 14920, Damascus: 10990, Homs: 13792, Latakia: 2555, AlWadi: 2900 | 0 | 0 | 13106 |
| person_aidsource | Aleppo: 85, Damascus: 15, Homs: 126, Latakia: 40, AlWadi: 35 | 0 | 0 | 78 |
| person_asset | Aleppo: 1224, Damascus: 497, Homs: 1103, Latakia: 197, AlWadi: 684 | 0 | 0 | 750 |
| person_checklistmembers | Aleppo: 10743, Damascus: 17477, Homs: 13428, Latakia: 4493, AlWadi: 3147 | 0 | 0 | 12974 |
| person_course | Aleppo: 351, Damascus: 312, Homs: 371, Latakia: 195, AlWadi: 40 | 0 | 0 | 145 |
| person_education | Aleppo: 2192, Damascus: 1327, Homs: 1793, Latakia: 419, AlWadi: 374 | 0 | 0 | 1664 |
| person_family | Aleppo: 14983, Damascus: 10985, Homs: 13786, Latakia: 2536, AlWadi: 2900 | 0 | 0 | 13076 |
| person_goals | Aleppo: 431, Damascus: 508, Homs: 1948, Latakia: 911, AlWadi: 545 | 0 | 0 | 843 |
| person_help | Aleppo: 1361, Damascus: 807, Homs: 825, Latakia: 124, AlWadi: 123 | 0 | 0 | 749 |
| person_illness | Aleppo: 595, Damascus: 735, Homs: 999, Latakia: 266, AlWadi: 286 | 0 | 0 | 697 |
| person_loss | Aleppo: 838, Damascus: 620, Homs: 826, Latakia: 185, AlWadi: 131 | 0 | 0 | 412 |
| person_microproject | Aleppo: 3988, Damascus: 2373, Homs: 2992, Latakia: 595, AlWadi: 645 | 0 | 0 | 2766 |
| person_powersource | Aleppo: 1263, Damascus: 252, Homs: 833, Latakia: 533, AlWadi: 471 | 0 | 0 | 506 |
| person_workexp | Aleppo: 8633, Damascus: 5531, Homs: 6992, Latakia: 1290, AlWadi: 1559 | 0 | 0 | 6756 |
| personstate | Aleppo: 8, Damascus: 8, Homs: 8, Latakia: 8, AlWadi: 8 | 1 | 0 | 0 |
| powersource | Aleppo: 7, Damascus: 7, Homs: 7, Latakia: 7, AlWadi: 7 | 1 | 0 | 0 |
| priest | Aleppo: 119, Damascus: 117, Homs: 60, Latakia: 38, AlWadi: 63 | 0 | 0 | 57 |
| profession_meeting | Aleppo: 1, Damascus: 0, Homs: 1, Latakia: 1, AlWadi: 0 | 0 | 1 | 1 |
| profession_meeting_answer | Aleppo: 4, Damascus: 0, Homs: 4, Latakia: 4, AlWadi: 0 | 0 | 1 | 4 |
| profession_meeting_attendee | Aleppo: 6, Damascus: 0, Homs: 5, Latakia: 3, AlWadi: 0 | 0 | 1 | 3 |
| profession_meeting_brand | Aleppo: 4, Damascus: 0, Homs: 2, Latakia: 1, AlWadi: 0 | 0 | 1 | 1 |
| profession_meeting_challenge | Aleppo: 1, Damascus: 0, Homs: 7, Latakia: 7, AlWadi: 0 | 0 | 1 | 1 |
| profession_meeting_competition | Aleppo: 3, Damascus: 0, Homs: 3, Latakia: 3, AlWadi: 0 | 0 | 1 | 2 |
| profession_meeting_equipment | Aleppo: 5, Damascus: 0, Homs: 1, Latakia: 1, AlWadi: 0 | 0 | 1 | 1 |
| profession_meeting_minute | Aleppo: 5, Damascus: 0, Homs: 5, Latakia: 5, AlWadi: 0 | 0 | 1 | 5 |
| profession_meeting_option_answer | Aleppo: 22, Damascus: 0, Homs: 16, Latakia: 14, AlWadi: 0 | 0 | 1 | 14 |
| profession_meeting_participant | Aleppo: 12, Damascus: 0, Homs: 8, Latakia: 6, AlWadi: 0 | 0 | 1 | 6 |
| profession_meeting_season | Aleppo: 1, Damascus: 0, Homs: 1, Latakia: 1, AlWadi: 0 | 0 | 1 | 1 |
| profession_option | Aleppo: 44, Damascus: 44, Homs: 44, Latakia: 44, AlWadi: 44 | 1 | 0 | 0 |
| profession_question | Aleppo: 22, Damascus: 22, Homs: 22, Latakia: 22, AlWadi: 22 | 1 | 0 | 0 |
| profession_section | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 1 | 0 | 0 |
| purchasing | Aleppo: 0, Damascus: 0, Homs: 0, Latakia: 0, AlWadi: 0 | 0 | 1 | 0 |
| purchasingcategory | Aleppo: 0, Damascus: 0, Homs: 0, Latakia: 0, AlWadi: 0 | 0 | 1 | 0 |
| rehabanswer | Aleppo: 56, Damascus: 56, Homs: 56, Latakia: 56, AlWadi: 56 | 1 | 0 | 0 |
| rehabquestion | Aleppo: 24, Damascus: 24, Homs: 24, Latakia: 24, AlWadi: 24 | 1 | 0 | 0 |
| rehabvisit | Aleppo: 27, Damascus: 0, Homs: 0, Latakia: 0, AlWadi: 0 | 0 | 1 | 0 |
| relation | Aleppo: 27, Damascus: 27, Homs: 27, Latakia: 27, AlWadi: 27 | 1 | 0 | 0 |
| resultstable | Homs: 717, AlWadi: 717 | 1 | 0 | 0 |
| risk | Aleppo: 3, Damascus: 3, Homs: 3, Latakia: 3, AlWadi: 3 | 1 | 0 | 0 |
| role | Aleppo: 8, Damascus: 8, Homs: 8, Latakia: 8, AlWadi: 8 | 1 | 0 | 0 |
| score | Aleppo: 10, Damascus: 10, Homs: 10, Latakia: 10, AlWadi: 10 | 1 | 0 | 0 |
| scorevalue | Aleppo: 30, Damascus: 30, Homs: 30, Latakia: 30, AlWadi: 30 | 1 | 0 | 0 |
| specialcase | Aleppo: 108, Damascus: 104, Homs: 264, Latakia: 0, AlWadi: 19 | 0 | 0 | 103 |
| specialcasetype | Aleppo: 2, Damascus: 2, Homs: 2, Latakia: 2, AlWadi: 2 | 1 | 0 | 0 |
| state | Aleppo: 11, Damascus: 11, Homs: 11, Latakia: 11, AlWadi: 11 | 1 | 0 | 0 |
| statereason | Aleppo: 25, Damascus: 25, Homs: 25, Latakia: 25, AlWadi: 25 | 2 | 0 | 2 |
| street | Aleppo: 60, Damascus: 45, Homs: 70, Latakia: 48, AlWadi: 24 | 0 | 0 | 60 |
| subcategory | Aleppo: 17, Damascus: 17, Homs: 17, Latakia: 17, AlWadi: 17 | 1 | 0 | 0 |
| t1 | Aleppo: 893, Damascus: 357, Homs: 1069, Latakia: 81, AlWadi: 98 | 0 | 0 | 0 |
| task | Aleppo: 39, Damascus: 39, Homs: 39, Latakia: 39, AlWadi: 39 | 1 | 0 | 0 |
| task_microproject | Aleppo: 48541, Damascus: 25788, Homs: 46397, Latakia: 12137, AlWadi: 10790 | 0 | 0 | 37410 |
| task_user | Aleppo: 76, Damascus: 73, Homs: 121, Latakia: 144, AlWadi: 132 | 0 | 0 | 62 |
| temp_gender | Damascus: 10969 | 0 | 0 | 0 |
| user | Aleppo: 58, Damascus: 45, Homs: 46, Latakia: 27, AlWadi: 47 | 0 | 0 | 41 |
| user_notification | Aleppo: 74702, Damascus: 46602, Homs: 110963, Latakia: 23075, AlWadi: 18714 | 0 | 0 | 26573 |
| version | Aleppo: 1, Damascus: 1, Homs: 1, Latakia: 1, AlWadi: 1 | 1 | 0 | 0 |
| visit | Aleppo: 1291, Damascus: 501, Homs: 1431, Latakia: 67, AlWadi: 33 | 0 | 0 | 1010 |
| visit_schedule_temp | Homs: 343 | 0 | 0 | 0 |
| visit_user | Aleppo: 1948, Damascus: 975, Homs: 1477, Latakia: 179, AlWadi: 33 | 0 | 0 | 743 |
| visitanswers | Aleppo: 43, Damascus: 43, Homs: 43, Latakia: 43, AlWadi: 43 | 1 | 0 | 0 |
| visitquestions | Aleppo: 20, Damascus: 20, Homs: 20, Latakia: 20, AlWadi: 20 | 1 | 0 | 0 |
| work | Aleppo: 111, Damascus: 123, Homs: 110, Latakia: 136, AlWadi: 101 | 0 | 0 | 36 |
| work_exp | Damascus: 2371 | 0 | 0 | 0 |
| worktype | Aleppo: 5, Damascus: 5, Homs: 5, Latakia: 5, AlWadi: 5 | 1 | 0 | 0 |

## Aggregate machine-readable evidence

The JSON below contains table names and aggregate counts only. Same-ID diagnostics never authorize sharing differing tables.

```json
{
  "method": "Exact decoded SQL-string/binary payload and numeric literal spelling; ordered source column/storage/nullability contract; multiset tuple hashes retain duplicates; schema syntax-only charset inference; no data normalization or SQL execution",
  "source_instances": 616,
  "source_columns": 3947,
  "rows_verified": 1721699,
  "distinct_names": 127,
  "nonempty_equal_groups": [
    {
      "table": "aganswer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 53,
      "saved_duplicate_occurrences": 212,
      "full_table_sha256": "c6664c83061c2d064c50cea1e807cf251ed612ab23ecd5fb13ae988976769f74",
      "schema_contract_sha256": "6966f6822b2d74c54dc645863a963e73f910f5c9bb150266047c399fb33e7cc8",
      "declared_parent_closure": [
        "agquestion"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "age",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 6,
      "saved_duplicate_occurrences": 24,
      "full_table_sha256": "918cdb384292496c26e357e41c5c2a8d74c93a1c5f778723f7f858f4471742cb",
      "schema_contract_sha256": "3e8ae9f37d19f775602d4a6f4e2818d7e8f71860eb419e1114490d3070fdec81",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "agquestion",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 14,
      "saved_duplicate_occurrences": 56,
      "full_table_sha256": "dc6688c829ce25d2e8b4525c49c2bccfa7bf63ac64aacaeb65d2857b17616fa3",
      "schema_contract_sha256": "d65f3a383d6d7b664e2ddc33f2fbf20044d4c9c47fb6a96efb0b6db9f54db08a",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "aid",
      "cities": [
        "Aleppo",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 20,
      "saved_duplicate_occurrences": 40,
      "full_table_sha256": "58513be9add5756cc7d460bb076c536005f90cb7d1ea9999c930bc91a82630b3",
      "schema_contract_sha256": "02b0dc652e230a071e932f8afdfc0630092b58e1e39c71d522834a9720301cfe",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "aidperiod",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 7,
      "saved_duplicate_occurrences": 28,
      "full_table_sha256": "2d8aa43b9b420e87644c990a8c38e0081b2c197c499cb4a4cb7578d1620a7c17",
      "schema_contract_sha256": "02b0dc652e230a071e932f8afdfc0630092b58e1e39c71d522834a9720301cfe",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "asset",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 4,
      "saved_duplicate_occurrences": 16,
      "full_table_sha256": "ca4cbde78f8faaef882d11c2adb0794b3759e43e50187d64a3209c93e10e26da",
      "schema_contract_sha256": "14f2313f515ba0b7b141e68ba42bd1f845ccd9f98915374ae077adb217b21dbd",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "assetbenefits",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 3,
      "saved_duplicate_occurrences": 12,
      "full_table_sha256": "31b10dfbf367bc61af04cfa62b6b479fedf9cb197ccbe2e6eb412ad674a5cc4b",
      "schema_contract_sha256": "c7664de388f737c1395c9ea2dcb8b726d12bf0aaf172b21e702fd3dc73b57289",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "assettype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 7,
      "saved_duplicate_occurrences": 28,
      "full_table_sha256": "9462d560147e3e200f5c5acd2c6bb2d336ce302cfb158eb115d9e4719898851e",
      "schema_contract_sha256": "29702b61cd1c4e48dea43ab21a04490aaa6f6ad45d43ab105354bbc2d7501048",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "backgrounds",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 1,
      "saved_duplicate_occurrences": 4,
      "full_table_sha256": "ff069e737550265188af7b0708eb4a773d0ebe0617ee7bf9ffc7532d58d77f34",
      "schema_contract_sha256": "fa9e30fcbcd6dcd40e018d6c17ca437a96ce52788b6844b7729b5dc604c93116",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "career",
      "cities": [
        "Homs",
        "AlWadi"
      ],
      "rows_per_city": 289,
      "saved_duplicate_occurrences": 289,
      "full_table_sha256": "725a4df62cbe03387495a54f847bc166a9f16519418a90f9c8e91d63434485f7",
      "schema_contract_sha256": "d1f38e83a2e26ae1bf4aa2b0789abbf034f5fdf396f813bd646b4bcc2c2bf48d",
      "declared_parent_closure": [
        "category",
        "subcategory"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "category",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 6,
      "saved_duplicate_occurrences": 24,
      "full_table_sha256": "c0f6fdec9cef6255c329b5a29085548b6c55d4c5a4e788a61c7e2f3eca7097b7",
      "schema_contract_sha256": "7605aa0d9179d25c9069518c60f4a9cb0405650f7ea956e1c030e41c77aefdef",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "checklist_opinion",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 3,
      "saved_duplicate_occurrences": 12,
      "full_table_sha256": "6ad456764884146a526142d18cf05f7b0084648ae8a094a8d0d163c277f35272",
      "schema_contract_sha256": "20876f7bb8fc697bc66272bc825e7161ae7007908f2f6b5c3b86aef586745e6c",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Relationship/detail workflow"
    },
    {
      "table": "course",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 4,
      "saved_duplicate_occurrences": 16,
      "full_table_sha256": "ea9e73abdcff8f70a0706e85492463259d020277bc4a55f06e5d8bd7d3458916",
      "schema_contract_sha256": "53db0b7a42cf585ffd1b4e3b3f910a534427ccf25dc930205c634e8f0895c82d",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "databases",
      "cities": [
        "Damascus",
        "Homs",
        "AlWadi"
      ],
      "rows_per_city": 4,
      "saved_duplicate_occurrences": 8,
      "full_table_sha256": "27af813534f77120e2f34c5423eb2ec3eaf01096b8173bd3f12e4662e74a7ec4",
      "schema_contract_sha256": "b81514b9e907a17f6b1f03e24897f1a6a9f145935749adb44daa09b6ba71bfb3",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Identity/configuration/audit/notification"
    },
    {
      "table": "donor",
      "cities": [
        "Aleppo",
        "Latakia"
      ],
      "rows_per_city": 15,
      "saved_duplicate_occurrences": 15,
      "full_table_sha256": "37a5347f9c4334c7331c5c80cec000a7508d7b73c7d98aa5cc086af6e4c3f826",
      "schema_contract_sha256": "7d8a1e589b4c775cbb0be0b60331c0f65319bda4b7e5ff9d6cc09530c33159b3",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "educationlevel",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 4,
      "saved_duplicate_occurrences": 16,
      "full_table_sha256": "e8b683a6c07697ca203eb9671d4a89639da43a2f69c766fcd219a5c60c894907",
      "schema_contract_sha256": "fa0521cd50a7b3319ae11bafb6ba4b942596d448912bf9ce215056ab01f17cba",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "educationtype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 20,
      "full_table_sha256": "e3d1381a354e59a360a8f23badaf7dabefb71eca7e81e5a0c818d81ef2cb4100",
      "schema_contract_sha256": "9933da282e145d2733952eda41be9e0fc597fbd26dbb274c032ead3d3ca3437b",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "fundtype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 2,
      "saved_duplicate_occurrences": 8,
      "full_table_sha256": "a2ae67c050268434db710525e3d1a6f1ef5c0631a55f3e330c588b9ec8c6bbc2",
      "schema_contract_sha256": "db549cc44453a6e37338b534e29a9e32153c9a3690d8339c214279227facb3d1",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "goal",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 15,
      "saved_duplicate_occurrences": 60,
      "full_table_sha256": "de34feddb9d172efef51767bcf202edd430cf965a68b1226c31d729bdb729565",
      "schema_contract_sha256": "822f623adcc4a288f5bc41dc8e7a63945171e0594b1e62cfa2df2da915a81b19",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "illness",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 20,
      "full_table_sha256": "cd5b415fea1d782885e4d39292872315dda9d4a8005bfa010168e8f857888a5d",
      "schema_contract_sha256": "13ccf4af584c0e2fd639545bc28741b561ee49ee2a8bbd26962cd9bdbf564cd9",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "imagetype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 12,
      "saved_duplicate_occurrences": 48,
      "full_table_sha256": "2a2f90a80bc808845a2d3513a7993581e1e9832f9d68abfa933ef64d1d34a7db",
      "schema_contract_sha256": "b1715d1f4da7262aeed940b1ab7ba138793e618b8d556b5165c5ea0d482cf666",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "incomesource",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 4,
      "saved_duplicate_occurrences": 16,
      "full_table_sha256": "b4c3d5a471e2a229b42b02c6e2509819fad46d06e5b1f2b5bd84fd06f3043aa7",
      "schema_contract_sha256": "7605aa0d9179d25c9069518c60f4a9cb0405650f7ea956e1c030e41c77aefdef",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "language",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 11,
      "saved_duplicate_occurrences": 44,
      "full_table_sha256": "c939de3a91d0d3ed1aa7fd3c9c48ff3fdbb54e2486b3cf180392bce6e8bfdf05",
      "schema_contract_sha256": "128989fc25a86479005065e2a244140cce4c647f3dd18078784a172fa50f3441",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "level",
      "cities": [
        "Aleppo",
        "Damascus",
        "Latakia"
      ],
      "rows_per_city": 4,
      "saved_duplicate_occurrences": 8,
      "full_table_sha256": "c23005bb34c2b09b1d7dbb90db98eb0dc3649631ba5eee3c578ca6096e9744c0",
      "schema_contract_sha256": "757237bdf125d595f2ce907fa18ae4d8b51a01747cd22f9d4ff1081996a25efa",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "level",
      "cities": [
        "Homs",
        "AlWadi"
      ],
      "rows_per_city": 4,
      "saved_duplicate_occurrences": 4,
      "full_table_sha256": "84ec0cf5ce3887583dad4f768769b35401856c1c4df24f0e064db546fcb775dc",
      "schema_contract_sha256": "757237bdf125d595f2ce907fa18ae4d8b51a01747cd22f9d4ff1081996a25efa",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "loss",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 20,
      "full_table_sha256": "7e4d6c8b27d523bf5a37fc732b16ebaebb9c4099a5401d048e786446fd353a53",
      "schema_contract_sha256": "7751e6bdba5e9c3c666219658606297870e2b9a866ca17eac58e6ce1230627ef",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "manualfile",
      "cities": [
        "Aleppo",
        "Latakia"
      ],
      "rows_per_city": 1,
      "saved_duplicate_occurrences": 1,
      "full_table_sha256": "1a95a689522a144acb594370c07113f83b99e0b044e38e4eb6874f9c85e471dc",
      "schema_contract_sha256": "6f49ecd1ab829786c147023ef1681db1c7b042ecef99a9db8b195b6279c4086e",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Attachment metadata/BLOB"
    },
    {
      "table": "maritalstatus",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 7,
      "saved_duplicate_occurrences": 28,
      "full_table_sha256": "df34888db85de0e83ef5c2f65093724eef7397cd22ca1523de01f97e7928c00f",
      "schema_contract_sha256": "b7973205e5638152be67e7c2254cc433de402068e2bf3b8bf4aebe045bfe46fd",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "meanswer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 104,
      "saved_duplicate_occurrences": 416,
      "full_table_sha256": "0e30cd66e0777779005ae185d3250ff367f499e44f2cdd99937f9742cc755818",
      "schema_contract_sha256": "7a2c9fcfb0ed16d10fafa3c237a46cb1500772adc417f046d18fbb1391bdae4f",
      "declared_parent_closure": [
        "mequestion"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "medicalneeds",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 20,
      "full_table_sha256": "f0c6229a2a3f41558f48715e7665989e7c9a6d89bf3683a11ae18a86bb0a53cb",
      "schema_contract_sha256": "02b0dc652e230a071e932f8afdfc0630092b58e1e39c71d522834a9720301cfe",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "meprofitmarks",
      "cities": [
        "Aleppo",
        "Latakia"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 5,
      "full_table_sha256": "608ea3ee1946b81368ea2545c93b142b43b96dc1ed528894b4144c3570cebe02",
      "schema_contract_sha256": "a73d7508cf30e3cbf6e2ae93c1ee3cd7d37afdb14086384940fe52e7b7d109f8",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "meprofitmarks",
      "cities": [
        "Damascus",
        "Homs",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 10,
      "full_table_sha256": "75d0159677679e4d734e4524b41f982c3ecc00405a560c7a5bb3d067e8b56c24",
      "schema_contract_sha256": "a73d7508cf30e3cbf6e2ae93c1ee3cd7d37afdb14086384940fe52e7b7d109f8",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "mequestion",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 42,
      "saved_duplicate_occurrences": 168,
      "full_table_sha256": "c86604943c3fb2e164f1b8224401363042733b376642f295339ef5a065ec7b1a",
      "schema_contract_sha256": "5e6e34b4cce06a1b6b746447a7e7edb9d2895fa8d5283107d743025e5cdb2f8a",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "meresultranges",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 4,
      "saved_duplicate_occurrences": 16,
      "full_table_sha256": "ae810586a238d37fe18eb1b57b1fde9b3ef0494f128b374d365b1021ffa4d21b",
      "schema_contract_sha256": "2b23bebf5e9d8167c7271deb8e4b08037238195007f6f83934ed2f31c9c3aacb",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "microprojectsubtype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 17,
      "saved_duplicate_occurrences": 68,
      "full_table_sha256": "37f6cbbff62695882cea5ec9acc4333739613ba00df8ef38373c3427cad23e73",
      "schema_contract_sha256": "a62fabda597097a9c7d15503069d19a0c59e272f71b67211b9195973e5c9e92a",
      "declared_parent_closure": [
        "microprojecttype"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "microprojecttype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 20,
      "full_table_sha256": "de3584590a5b0f9769119d589b85f7fb1634248cce7081759aef205e26489ded",
      "schema_contract_sha256": "db549cc44453a6e37338b534e29a9e32153c9a3690d8339c214279227facb3d1",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "militarystate",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 20,
      "full_table_sha256": "f4aa76f54dcf332148f7fee381e7d37e54066d6ee96c37dec080e7e43146377b",
      "schema_contract_sha256": "db549cc44453a6e37338b534e29a9e32153c9a3690d8339c214279227facb3d1",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "parish",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 11,
      "saved_duplicate_occurrences": 44,
      "full_table_sha256": "cc8aa5c5b2760197871a709aa4f9b6e66f1773d3d18f61f13c1bacdc396b86c0",
      "schema_contract_sha256": "f368e5f98dd23e5fe8f7a31e6a522d6876209584bf20d3d8a1f6eb3ec8d82c57",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "personstate",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 8,
      "saved_duplicate_occurrences": 32,
      "full_table_sha256": "0e978aed6864bb63e227b4d38a193c5d58e1654e9169ba67c58d70447cbe4dbe",
      "schema_contract_sha256": "543ac2cdeb9dd5df37620f186007bc654a1be14db4dfd4ab21ca53b31d8c3a45",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "powersource",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 7,
      "saved_duplicate_occurrences": 28,
      "full_table_sha256": "62d936a3bdc9f18d3702c20f27a48e184579f712d812f7cfd8f538ff06a1b78f",
      "schema_contract_sha256": "ecb882e777ba988a5ce87a35be1ad0559c21143e66e50cead1a39b41944e1f41",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "profession_option",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 44,
      "saved_duplicate_occurrences": 176,
      "full_table_sha256": "426708e97066444e5f2d7121acd254b1b05250544568702b76266ec7f751bb53",
      "schema_contract_sha256": "f394c596e46a1e8bccab79641c3093290bf640beca10588f6ff62ca489ec8d96",
      "declared_parent_closure": [
        "profession_question",
        "profession_section"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "profession_question",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 22,
      "saved_duplicate_occurrences": 88,
      "full_table_sha256": "b9890c6a0740a39c7aca83391fe8ee4fe09b856bebf18bb9e9c4b8408e0a3934",
      "schema_contract_sha256": "d12bbf6781df9d2c97fce1becccda97e0a3aa537ca0343d9414726d4c04192ab",
      "declared_parent_closure": [
        "profession_section"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "profession_section",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 20,
      "full_table_sha256": "aaee52d1ff0ea24b9ab0c6a99f30d66c1701901eaa1b76889a03af4374ab1107",
      "schema_contract_sha256": "951e3abfe00f5835e391af2198811edfefb6254fb252c7735555156b780d8e53",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "rehabanswer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 56,
      "saved_duplicate_occurrences": 224,
      "full_table_sha256": "77635ae8f236fcd51a3cca0896578a3cb30f8f28c6ea4ffebf84bf3cdc005914",
      "schema_contract_sha256": "238be3d3f3602f6df34bf122c3da4b9dda4636d9423a6080157b0d4fc756a3e7",
      "declared_parent_closure": [
        "rehabquestion"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "rehabquestion",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 24,
      "saved_duplicate_occurrences": 96,
      "full_table_sha256": "0e96eb865a569aaee529b5a34c9d5fe3450eb10023a85a3fa93a9954eb972844",
      "schema_contract_sha256": "4c376144fd1f3810c259d630b47d97ed623994263a30cdb483c76275ede20f78",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "relation",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 27,
      "saved_duplicate_occurrences": 108,
      "full_table_sha256": "718244992b8379cbac7233d4cffc5a9fdd3beef3e8a15172f0cca2e12cfb511a",
      "schema_contract_sha256": "9933da282e145d2733952eda41be9e0fc597fbd26dbb274c032ead3d3ca3437b",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "resultstable",
      "cities": [
        "Homs",
        "AlWadi"
      ],
      "rows_per_city": 717,
      "saved_duplicate_occurrences": 717,
      "full_table_sha256": "e1dd11b08364ed9cf059afba2b48984c855979eda6acbd3ed2c435f4b1849840",
      "schema_contract_sha256": "d97dd0751faecdaab920c6736adde73219a723b7e1b1d894b5798b58d9af35d6",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Uncertain/auxiliary; retain, investigate before disposition"
    },
    {
      "table": "risk",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 3,
      "saved_duplicate_occurrences": 12,
      "full_table_sha256": "2320634010fb320bca67af342724ae4bb7ff449ac321a55e0d6de29526569ed9",
      "schema_contract_sha256": "d65f3a383d6d7b664e2ddc33f2fbf20044d4c9c47fb6a96efb0b6db9f54db08a",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "role",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 8,
      "saved_duplicate_occurrences": 32,
      "full_table_sha256": "a17ad3ba80cf0968d2254cb8bda4df7f373d0a152e53ea0e0c80a614aee4e74d",
      "schema_contract_sha256": "08364f0df802681671ba839f3966e5328c55079f48fd3b6f286171725c85f278",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Identity/configuration/audit/notification"
    },
    {
      "table": "score",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 10,
      "saved_duplicate_occurrences": 40,
      "full_table_sha256": "37b2432d92787fa7379dc39c7d7415d9d5c862b2af9b02bffec615752d1a2933",
      "schema_contract_sha256": "e8b324f509413cb65e6ea82d666f8f68059869cf2eda53a2dbf4b456c830690f",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "scorevalue",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 30,
      "saved_duplicate_occurrences": 120,
      "full_table_sha256": "c58672206153bdf6f36e5b23d783ac03dab477a63d0d95e87bdbae698af0dc40",
      "schema_contract_sha256": "bf9a5448c7abd8f54fafd6053d656a44a5b3426ec1033b3cfee14acc691865b9",
      "declared_parent_closure": [
        "score"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "specialcasetype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 2,
      "saved_duplicate_occurrences": 8,
      "full_table_sha256": "0f7081f6ee6930d6d63eb69cd42382e2b85e0f94be68b27536d1dad407f446fe",
      "schema_contract_sha256": "9933da282e145d2733952eda41be9e0fc597fbd26dbb274c032ead3d3ca3437b",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "state",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 11,
      "saved_duplicate_occurrences": 44,
      "full_table_sha256": "716fdb610e5f11f94a172111080d1e69dfe98266f9502aecea57378d897ab567",
      "schema_contract_sha256": "5447e1b4704946bf0913bc769d81e33ba501534964ddc6254d517600fe387c44",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "statereason",
      "cities": [
        "Damascus",
        "Latakia"
      ],
      "rows_per_city": 25,
      "saved_duplicate_occurrences": 25,
      "full_table_sha256": "872f8a4ff1b2e643a8a2f9c6330dbb5b89dcdef7f524cf8a359ce0f91ce3ae62",
      "schema_contract_sha256": "85e250979da2f53b9838e4fffd40a9bd69f032918da382c21b3920b329ce03bf",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "statereason",
      "cities": [
        "Homs",
        "AlWadi"
      ],
      "rows_per_city": 25,
      "saved_duplicate_occurrences": 25,
      "full_table_sha256": "26f2445104f594dfa92f87bf6a704c0f4f42ab32f99dd8d016fdcc33ead55a03",
      "schema_contract_sha256": "85e250979da2f53b9838e4fffd40a9bd69f032918da382c21b3920b329ce03bf",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "subcategory",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 17,
      "saved_duplicate_occurrences": 68,
      "full_table_sha256": "76c6c4b6c51aad5b4fb17d7eb7b0d9e09fc98ed7391e14f5115ddcb855318352",
      "schema_contract_sha256": "4cd64a87ad133563d2963e8fcda7ec688809adb7176238296222ff4495824eec",
      "declared_parent_closure": [
        "category"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "task",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 39,
      "saved_duplicate_occurrences": 156,
      "full_table_sha256": "e0be93258f8d9d96165ac79c751ac75dbf13772fcd0f8723ab5c062ae62c2caf",
      "schema_contract_sha256": "6a218495e5978ee637606fe7764eed24e8d08e63ccb526eaa620a1b8963555ff",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Staff task workflow"
    },
    {
      "table": "version",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 1,
      "saved_duplicate_occurrences": 4,
      "full_table_sha256": "9a21d47989f2323e431a9008033071d5ebbb464fd4db09fe981a817129274463",
      "schema_contract_sha256": "6a280bb322414fab6a902696536643b593cfb2d3cf9024048a7cbd1d38399497",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Identity/configuration/audit/notification"
    },
    {
      "table": "visitanswers",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 43,
      "saved_duplicate_occurrences": 172,
      "full_table_sha256": "e1ff23fc9fe0a31fe05a490355bd9f69cc5188350ab34d0e8b9f5b89b4826a34",
      "schema_contract_sha256": "2f9f0247ce0ee7fae33af0b260129373aa49c75864834122200bd2f0100a3d24",
      "declared_parent_closure": [
        "visitquestions"
      ],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "visitquestions",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 20,
      "saved_duplicate_occurrences": 80,
      "full_table_sha256": "20499969daa2caa328e8c270f4997019454fa37d0d66988f2279aa2285fcb73b",
      "schema_contract_sha256": "8032fbd5c4c6377e0a633ef39e09ebbbd5b30dd6e8d2a327cbbebaf95109ab8e",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "worktype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 5,
      "saved_duplicate_occurrences": 20,
      "full_table_sha256": "604baca8e8ae537e21e908ecbcee7178486606fbfb181cdc8c9de293d1f60b16",
      "schema_contract_sha256": "3dd6d8e06442a5d9b79a39f0242d23fecc7c0255acd36c08e5101eba3e72ee4e",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    }
  ],
  "empty_equal_groups": [
    {
      "table": "exefile",
      "cities": [
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "485f994d0955904c322ec913c806383649a55dc17dfdbc44a66d81dfd51fb358",
      "schema_contract_sha256": "04f846475df04ce6293a054442070cd680565b3a9dadac40198cec87eb92cee6",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "microproject",
        "microprojectsubtype",
        "microprojecttype",
        "role",
        "street",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "street",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Finance/execution transaction"
    },
    {
      "table": "file",
      "cities": [
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "189574ae6e8313e91c4fed15ff6ca6ef189e1672dae69d609075b8be2ede28a9",
      "schema_contract_sha256": "8ddfb7ce0eb348954f1c812fccf52c38d15c1515dd141209f62bdf387ac34ddb",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "microproject",
        "microprojectsubtype",
        "microprojecttype",
        "role",
        "street",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "street",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Attachment metadata/BLOB"
    },
    {
      "table": "manualfile",
      "cities": [
        "Damascus",
        "Homs",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "403c0705b4a687859db3c12e0a0d602d1c2c91f593e18b1ba758f23950d9eecf",
      "schema_contract_sha256": "6f49ecd1ab829786c147023ef1681db1c7b042ecef99a9db8b195b6279c4086e",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Attachment metadata/BLOB"
    },
    {
      "table": "microproject_ag_answer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "6d4685499708a3cadad91e84ee9f512031a6e2711d2c21491e0434aa0abe5304",
      "schema_contract_sha256": "c7ff47007d0a8e72dd952839475ce0ac03f9f0b1a254e081f75029d4021a19e7",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "microproject",
        "microprojectsubtype",
        "microprojecttype",
        "role",
        "street",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "street",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Relationship/detail workflow"
    },
    {
      "table": "microproject_rehab",
      "cities": [
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "12b8875096369dcdf40a6e1173f6196aff77d1ad49fc0ac54ab6074b6f5e4efc",
      "schema_contract_sha256": "22e7ac422284b2049df64cb6447e82f33e9ffc59ad90570fb042918e15ed013d",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "microproject",
        "microprojectsubtype",
        "microprojecttype",
        "role",
        "street",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "street",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Relationship/detail workflow"
    },
    {
      "table": "microproject_rehab_answer",
      "cities": [
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "5bfef3382545a5f592e4a3ebcd4c84c832fc04df1556702de4add3b1cc621c10",
      "schema_contract_sha256": "44ee922e86bcd825ce5ce34d0572295f6c1c966be84abc31b5f5a59ffd4334b3",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "microproject",
        "microproject_rehab",
        "microprojectsubtype",
        "microprojecttype",
        "rehabanswer",
        "rehabquestion",
        "role",
        "street",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "street",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Relationship/detail workflow"
    },
    {
      "table": "profession_meeting",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "262d7566bcb884e9ac76c80b19d935f6ecf15fe5f59ef5cc31937a03a4db7411",
      "schema_contract_sha256": "f9db8d0ce53f6be0eee66425cb0c22317042201a45a0c1976fa218301592ab89",
      "declared_parent_closure": [
        "career",
        "category",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_answer",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "4ceae75484277e7efafbdfee36cab78fbc554608297801f0d1d27b6e5b807280",
      "schema_contract_sha256": "7ddcf5c2915620325fb2862c57240d72c45444817f8a5420be3870f77edf569b",
      "declared_parent_closure": [
        "career",
        "category",
        "profession_meeting",
        "profession_question",
        "profession_section",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_attendee",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "a38b59e3b887c0830edde4c324584c5324b234adc44c5d3ca7671d46f6e0ea8a",
      "schema_contract_sha256": "f76ff6c2a104bcb474799a477bb83d245c13f8e7e673f75fca5cf45fe29a23f1",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "maritalstatus",
        "microproject",
        "microprojectsubtype",
        "microprojecttype",
        "parish",
        "person",
        "personstate",
        "priest",
        "profession_meeting",
        "role",
        "street",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "person",
        "priest",
        "street",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_brand",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "318136e056763daf73c52e7ed1f9ad6edcde0bf594e9d46520ea4cd81f0e0bbf",
      "schema_contract_sha256": "05aa30942107175e22a79e37f2375633f4fb2ab1c470aecbf8dc04e474e68c30",
      "declared_parent_closure": [
        "career",
        "category",
        "profession_meeting",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_challenge",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "bf76f5d13a482ddcafb8cddc883f965261bd49091ee8105781458966c26e1d23",
      "schema_contract_sha256": "726563950c8888542a6f04909492878ca340c22cd5af27d43bcfc8a002b15e3e",
      "declared_parent_closure": [
        "career",
        "category",
        "profession_meeting",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_competition",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "a3f06e46936b857993fb24a8163f17cbe8b06b77f1a6703de9ae4e13aca06cda",
      "schema_contract_sha256": "995ea47bd58e2e5eab6e538d8a1fa5fb621cbee42c1c3a2dfdf54d09974f0719",
      "declared_parent_closure": [
        "career",
        "category",
        "profession_meeting",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_equipment",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "601fb6643ea7cfcf9257ec64c2c301692614b17b4af5ae4753d0a052b06a3a81",
      "schema_contract_sha256": "801a7cf3c5438383b05079845cd6ced4be59e1535ddccbd8d8f449b00587bd1c",
      "declared_parent_closure": [
        "career",
        "category",
        "profession_meeting",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_minute",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "7fd6fac241ea396ae61a229c074d4e7ea70789eb0ed3ad319e244e466bffb0f2",
      "schema_contract_sha256": "25f1f138da5fd539586309aa29fd578c77e0032285fe586fe058caebbc4b295f",
      "declared_parent_closure": [
        "career",
        "category",
        "profession_meeting",
        "profession_section",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_option_answer",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "6c83cffeb73db080cb1c51bbd2fd67c2292f71313a16c54ee73065198bff4cbe",
      "schema_contract_sha256": "59c37ba92b1996b16c02062191559d422e47fe9c033bdfeb3ec79891bdde0b55",
      "declared_parent_closure": [
        "career",
        "category",
        "profession_meeting",
        "profession_option",
        "profession_question",
        "profession_section",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_participant",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "6095e65927cda18752d2bd8d7374d1087e96ef8d4eb23fe297ab3248ea660d37",
      "schema_contract_sha256": "a74f9719f9574187fb0f1467dec084a1bc5fc2e6d662523197d158d8654343ca",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "maritalstatus",
        "microproject",
        "microprojectsubtype",
        "microprojecttype",
        "parish",
        "person",
        "personstate",
        "priest",
        "profession_meeting",
        "profession_question",
        "profession_section",
        "role",
        "street",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "person",
        "priest",
        "street",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "profession_meeting_season",
      "cities": [
        "Damascus",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "b5e8e5df1fabc37a6a64ad35ee27014314488c862c41a76d634c84159ff887a6",
      "schema_contract_sha256": "4f78f15fd5ba8e60ac962495d3f59386ed5c53d4786cd1b86824dc6e45f3a697",
      "declared_parent_closure": [
        "career",
        "category",
        "profession_meeting",
        "role",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Profession meeting workflow"
    },
    {
      "table": "purchasing",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "60ee869e006e5d5ff65fdb41ad6a547b27f9e04d720eb5e843fea0dfaf77a5c6",
      "schema_contract_sha256": "e163af6d991d42c0fbe2ef0c4c2d4f2b41991c040ec9db1eca08c2aee8eb184f",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "microproject",
        "microprojectsubtype",
        "microprojecttype",
        "purchasingcategory",
        "role",
        "street",
        "subcategory",
        "user"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "street",
        "user"
      ],
      "dependency_closed": false,
      "classification": "Finance/execution transaction"
    },
    {
      "table": "purchasingcategory",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "535eae80d3cfb8f31cc6d1d17265d9e43efb2fb2cd7221a08dc8fe932c13d2a7",
      "schema_contract_sha256": "9933da282e145d2733952eda41be9e0fc597fbd26dbb274c032ead3d3ca3437b",
      "declared_parent_closure": [],
      "unmatched_parents": [],
      "dependency_closed": true,
      "classification": "Reference/lookup/questionnaire definition; retain source-qualified"
    },
    {
      "table": "rehabvisit",
      "cities": [
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "rows_per_city": 0,
      "saved_duplicate_occurrences": 0,
      "full_table_sha256": "033f5d3c35d99cfb78fce547d30d1f44db4f4aa42874691fe89a1db664e0a9c8",
      "schema_contract_sha256": "3b9e906118ebd27289ca3280429c1935a4f77be7633d1b94aa3ad8dac0c4b870",
      "declared_parent_closure": [
        "assettype",
        "career",
        "category",
        "donor",
        "donorgroup",
        "fundtype",
        "microproject",
        "microprojectsubtype",
        "microprojecttype",
        "role",
        "street",
        "subcategory",
        "user",
        "visit"
      ],
      "unmatched_parents": [
        "career",
        "donor",
        "donorgroup",
        "microproject",
        "street",
        "user",
        "visit"
      ],
      "dependency_closed": false,
      "classification": "Core/workflow record"
    }
  ],
  "table_coverage": [
    {
      "table": "aganswer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 53,
        "Damascus": 53,
        "Homs": 53,
        "Latakia": 53,
        "AlWadi": 53
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "age",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 6,
        "Damascus": 6,
        "Homs": 6,
        "Latakia": 6,
        "AlWadi": 6
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "agquestion",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 14,
        "Damascus": 14,
        "Homs": 14,
        "Latakia": 14,
        "AlWadi": 14
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "aid",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 20,
        "Damascus": 22,
        "Homs": 21,
        "Latakia": 20,
        "AlWadi": 20
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 1
    },
    {
      "table": "aidperiod",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 7,
        "Damascus": 7,
        "Homs": 7,
        "Latakia": 7,
        "AlWadi": 7
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "aidsource",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 26,
        "Damascus": 34,
        "Homs": 35,
        "Latakia": 27,
        "AlWadi": 27
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 8
    },
    {
      "table": "asset",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4,
        "Damascus": 4,
        "Homs": 4,
        "Latakia": 4,
        "AlWadi": 4
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "assetbenefits",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 3,
        "Damascus": 3,
        "Homs": 3,
        "Latakia": 3,
        "AlWadi": 3
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "assettype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 7,
        "Damascus": 7,
        "Homs": 7,
        "Latakia": 7,
        "AlWadi": 7
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "backgrounds",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1,
        "Damascus": 1,
        "Homs": 1,
        "Latakia": 1,
        "AlWadi": 1
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "career",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 289,
        "Damascus": 283,
        "Homs": 289,
        "Latakia": 289,
        "AlWadi": 289
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 11
    },
    {
      "table": "category",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 6,
        "Damascus": 6,
        "Homs": 6,
        "Latakia": 6,
        "AlWadi": 6
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "checklist",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 3879,
        "Damascus": 2230,
        "Homs": 2504,
        "Latakia": 553,
        "AlWadi": 427
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 2504
    },
    {
      "table": "checklist_member",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 49,
        "Damascus": 42,
        "Homs": 48,
        "Latakia": 27,
        "AlWadi": 47
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 35
    },
    {
      "table": "checklist_opinion",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 3,
        "Damascus": 3,
        "Homs": 3,
        "Latakia": 3,
        "AlWadi": 3
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "checklist_position",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 25,
        "Damascus": 27,
        "Homs": 27,
        "Latakia": 17,
        "AlWadi": 28
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 16
    },
    {
      "table": "course",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4,
        "Damascus": 4,
        "Homs": 4,
        "Latakia": 4,
        "AlWadi": 4
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "databases",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 6,
        "Damascus": 4,
        "Homs": 4,
        "Latakia": 6,
        "AlWadi": 4
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 1
    },
    {
      "table": "dept",
      "cities": [
        "Damascus"
      ],
      "counts": {
        "Damascus": 2043
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "donor",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 15,
        "Damascus": 11,
        "Homs": 12,
        "Latakia": 15,
        "AlWadi": 10
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 3
    },
    {
      "table": "donorgroup",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 43,
        "Damascus": 23,
        "Homs": 57,
        "Latakia": 40,
        "AlWadi": 12
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 41
    },
    {
      "table": "education",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 96,
        "Damascus": 174,
        "Homs": 181,
        "Latakia": 104,
        "AlWadi": 178
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 81
    },
    {
      "table": "educationlevel",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4,
        "Damascus": 4,
        "Homs": 4,
        "Latakia": 4,
        "AlWadi": 4
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "educationtype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "exefile",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 145,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 0,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "family",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 3855,
        "Damascus": 2340,
        "Homs": 2914,
        "Latakia": 590,
        "AlWadi": 612
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 2784
    },
    {
      "table": "file",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 32,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 0,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "fundtype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 2,
        "Damascus": 2,
        "Homs": 2,
        "Latakia": 2,
        "AlWadi": 2
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "goal",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 15,
        "Damascus": 15,
        "Homs": 15,
        "Latakia": 15,
        "AlWadi": 15
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "illness",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "image",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 9511,
        "Damascus": 9960,
        "Homs": 19341,
        "Latakia": 6067,
        "AlWadi": 3486
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 9282
    },
    {
      "table": "imagetype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 12,
        "Damascus": 12,
        "Homs": 12,
        "Latakia": 12,
        "AlWadi": 12
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "incomesource",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4,
        "Damascus": 4,
        "Homs": 4,
        "Latakia": 4,
        "AlWadi": 4
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "language",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 11,
        "Damascus": 11,
        "Homs": 11,
        "Latakia": 11,
        "AlWadi": 11
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "level",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4,
        "Damascus": 4,
        "Homs": 4,
        "Latakia": 4,
        "AlWadi": 4
      },
      "nonempty_equal_groups": 2,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "loan",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 989,
        "Damascus": 544,
        "Homs": 1160,
        "Latakia": 199,
        "AlWadi": 161
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 950
    },
    {
      "table": "log",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 106082,
        "Damascus": 124885,
        "Homs": 291976,
        "Latakia": 48197,
        "AlWadi": 50990
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 106047
    },
    {
      "table": "loss",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "manualfile",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 1,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "maritalstatus",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 7,
        "Damascus": 7,
        "Homs": 7,
        "Latakia": 7,
        "AlWadi": 7
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "material",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 2988,
        "Damascus": 2159,
        "Homs": 2861,
        "Latakia": 4280,
        "AlWadi": 838
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 2948
    },
    {
      "table": "meanswer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 104,
        "Damascus": 104,
        "Homs": 104,
        "Latakia": 104,
        "AlWadi": 104
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "medicalneeds",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "meprofitmarks",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 2,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 4
    },
    {
      "table": "mequestion",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 42,
        "Damascus": 42,
        "Homs": 42,
        "Latakia": 42,
        "AlWadi": 42
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "meresultranges",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4,
        "Damascus": 4,
        "Homs": 4,
        "Latakia": 4,
        "AlWadi": 4
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "mevisit",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 2971,
        "Damascus": 1468,
        "Homs": 2940,
        "Latakia": 28,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 2606
    },
    {
      "table": "mevisit_answer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 60378,
        "Damascus": 31458,
        "Homs": 68610,
        "Latakia": 666,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 39779
    },
    {
      "table": "mevisit_user",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4247,
        "Damascus": 2319,
        "Homs": 3165,
        "Latakia": 52,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 1850
    },
    {
      "table": "microproject",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 3932,
        "Damascus": 2363,
        "Homs": 2974,
        "Latakia": 593,
        "AlWadi": 650
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 2974
    },
    {
      "table": "microproject_ag_answer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 0,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 0,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "microproject_powersource",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 2079,
        "Damascus": 78,
        "Homs": 160,
        "Latakia": 347,
        "AlWadi": 121
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 347
    },
    {
      "table": "microproject_rehab",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 2,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 0,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "microproject_rehab_answer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 48,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 0,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "microproject_risk",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 23,
        "Damascus": 60,
        "Homs": 206,
        "Latakia": 49,
        "AlWadi": 33
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 20
    },
    {
      "table": "microproject_score",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 35881,
        "Damascus": 20690,
        "Homs": 20930,
        "Latakia": 2060,
        "AlWadi": 3810
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 8129
    },
    {
      "table": "microproject_state",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 9606,
        "Damascus": 6148,
        "Homs": 7763,
        "Latakia": 2112,
        "AlWadi": 1522
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 5798
    },
    {
      "table": "microprojectsubtype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 17,
        "Damascus": 17,
        "Homs": 17,
        "Latakia": 17,
        "AlWadi": 17
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "microprojecttype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "militarystate",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "office_monitoring",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1178,
        "Damascus": 501,
        "Homs": 1431,
        "Latakia": 68,
        "AlWadi": 33
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 1176
    },
    {
      "table": "parish",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 11,
        "Damascus": 11,
        "Homs": 11,
        "Latakia": 11,
        "AlWadi": 11
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "payment",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 20656,
        "Damascus": 8216,
        "Homs": 20612,
        "Latakia": 1650,
        "AlWadi": 1087
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 19392
    },
    {
      "table": "person",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 14920,
        "Damascus": 10990,
        "Homs": 13792,
        "Latakia": 2555,
        "AlWadi": 2900
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 13106
    },
    {
      "table": "person_aidsource",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 85,
        "Damascus": 15,
        "Homs": 126,
        "Latakia": 40,
        "AlWadi": 35
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 78
    },
    {
      "table": "person_asset",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1224,
        "Damascus": 497,
        "Homs": 1103,
        "Latakia": 197,
        "AlWadi": 684
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 750
    },
    {
      "table": "person_checklistmembers",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 10743,
        "Damascus": 17477,
        "Homs": 13428,
        "Latakia": 4493,
        "AlWadi": 3147
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 12974
    },
    {
      "table": "person_course",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 351,
        "Damascus": 312,
        "Homs": 371,
        "Latakia": 195,
        "AlWadi": 40
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 145
    },
    {
      "table": "person_education",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 2192,
        "Damascus": 1327,
        "Homs": 1793,
        "Latakia": 419,
        "AlWadi": 374
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 1664
    },
    {
      "table": "person_family",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 14983,
        "Damascus": 10985,
        "Homs": 13786,
        "Latakia": 2536,
        "AlWadi": 2900
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 13076
    },
    {
      "table": "person_goals",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 431,
        "Damascus": 508,
        "Homs": 1948,
        "Latakia": 911,
        "AlWadi": 545
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 843
    },
    {
      "table": "person_help",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1361,
        "Damascus": 807,
        "Homs": 825,
        "Latakia": 124,
        "AlWadi": 123
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 749
    },
    {
      "table": "person_illness",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 595,
        "Damascus": 735,
        "Homs": 999,
        "Latakia": 266,
        "AlWadi": 286
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 697
    },
    {
      "table": "person_loss",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 838,
        "Damascus": 620,
        "Homs": 826,
        "Latakia": 185,
        "AlWadi": 131
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 412
    },
    {
      "table": "person_microproject",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 3988,
        "Damascus": 2373,
        "Homs": 2992,
        "Latakia": 595,
        "AlWadi": 645
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 2766
    },
    {
      "table": "person_powersource",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1263,
        "Damascus": 252,
        "Homs": 833,
        "Latakia": 533,
        "AlWadi": 471
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 506
    },
    {
      "table": "person_workexp",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 8633,
        "Damascus": 5531,
        "Homs": 6992,
        "Latakia": 1290,
        "AlWadi": 1559
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 6756
    },
    {
      "table": "personstate",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 8,
        "Damascus": 8,
        "Homs": 8,
        "Latakia": 8,
        "AlWadi": 8
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "powersource",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 7,
        "Damascus": 7,
        "Homs": 7,
        "Latakia": 7,
        "AlWadi": 7
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "priest",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 119,
        "Damascus": 117,
        "Homs": 60,
        "Latakia": 38,
        "AlWadi": 63
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 57
    },
    {
      "table": "profession_meeting",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1,
        "Damascus": 0,
        "Homs": 1,
        "Latakia": 1,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 1
    },
    {
      "table": "profession_meeting_answer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4,
        "Damascus": 0,
        "Homs": 4,
        "Latakia": 4,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 4
    },
    {
      "table": "profession_meeting_attendee",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 6,
        "Damascus": 0,
        "Homs": 5,
        "Latakia": 3,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 3
    },
    {
      "table": "profession_meeting_brand",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 4,
        "Damascus": 0,
        "Homs": 2,
        "Latakia": 1,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 1
    },
    {
      "table": "profession_meeting_challenge",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1,
        "Damascus": 0,
        "Homs": 7,
        "Latakia": 7,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 1
    },
    {
      "table": "profession_meeting_competition",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 3,
        "Damascus": 0,
        "Homs": 3,
        "Latakia": 3,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 2
    },
    {
      "table": "profession_meeting_equipment",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 0,
        "Homs": 1,
        "Latakia": 1,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 1
    },
    {
      "table": "profession_meeting_minute",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 0,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 5
    },
    {
      "table": "profession_meeting_option_answer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 22,
        "Damascus": 0,
        "Homs": 16,
        "Latakia": 14,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 14
    },
    {
      "table": "profession_meeting_participant",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 12,
        "Damascus": 0,
        "Homs": 8,
        "Latakia": 6,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 6
    },
    {
      "table": "profession_meeting_season",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1,
        "Damascus": 0,
        "Homs": 1,
        "Latakia": 1,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 1
    },
    {
      "table": "profession_option",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 44,
        "Damascus": 44,
        "Homs": 44,
        "Latakia": 44,
        "AlWadi": 44
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "profession_question",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 22,
        "Damascus": 22,
        "Homs": 22,
        "Latakia": 22,
        "AlWadi": 22
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "profession_section",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "purchasing",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 0,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 0,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "purchasingcategory",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 0,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 0,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "rehabanswer",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 56,
        "Damascus": 56,
        "Homs": 56,
        "Latakia": 56,
        "AlWadi": 56
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "rehabquestion",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 24,
        "Damascus": 24,
        "Homs": 24,
        "Latakia": 24,
        "AlWadi": 24
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "rehabvisit",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 27,
        "Damascus": 0,
        "Homs": 0,
        "Latakia": 0,
        "AlWadi": 0
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 1,
      "max_same_id_conflicts": 0
    },
    {
      "table": "relation",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 27,
        "Damascus": 27,
        "Homs": 27,
        "Latakia": 27,
        "AlWadi": 27
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "resultstable",
      "cities": [
        "Homs",
        "AlWadi"
      ],
      "counts": {
        "Homs": 717,
        "AlWadi": 717
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "risk",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 3,
        "Damascus": 3,
        "Homs": 3,
        "Latakia": 3,
        "AlWadi": 3
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "role",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 8,
        "Damascus": 8,
        "Homs": 8,
        "Latakia": 8,
        "AlWadi": 8
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "score",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 10,
        "Damascus": 10,
        "Homs": 10,
        "Latakia": 10,
        "AlWadi": 10
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "scorevalue",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 30,
        "Damascus": 30,
        "Homs": 30,
        "Latakia": 30,
        "AlWadi": 30
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "specialcase",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 108,
        "Damascus": 104,
        "Homs": 264,
        "Latakia": 0,
        "AlWadi": 19
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 103
    },
    {
      "table": "specialcasetype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 2,
        "Damascus": 2,
        "Homs": 2,
        "Latakia": 2,
        "AlWadi": 2
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "state",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 11,
        "Damascus": 11,
        "Homs": 11,
        "Latakia": 11,
        "AlWadi": 11
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "statereason",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 25,
        "Damascus": 25,
        "Homs": 25,
        "Latakia": 25,
        "AlWadi": 25
      },
      "nonempty_equal_groups": 2,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 2
    },
    {
      "table": "street",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 60,
        "Damascus": 45,
        "Homs": 70,
        "Latakia": 48,
        "AlWadi": 24
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 60
    },
    {
      "table": "subcategory",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 17,
        "Damascus": 17,
        "Homs": 17,
        "Latakia": 17,
        "AlWadi": 17
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "t1",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 893,
        "Damascus": 357,
        "Homs": 1069,
        "Latakia": 81,
        "AlWadi": 98
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "task",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 39,
        "Damascus": 39,
        "Homs": 39,
        "Latakia": 39,
        "AlWadi": 39
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "task_microproject",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 48541,
        "Damascus": 25788,
        "Homs": 46397,
        "Latakia": 12137,
        "AlWadi": 10790
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 37410
    },
    {
      "table": "task_user",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 76,
        "Damascus": 73,
        "Homs": 121,
        "Latakia": 144,
        "AlWadi": 132
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 62
    },
    {
      "table": "temp_gender",
      "cities": [
        "Damascus"
      ],
      "counts": {
        "Damascus": 10969
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "user",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 58,
        "Damascus": 45,
        "Homs": 46,
        "Latakia": 27,
        "AlWadi": 47
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 41
    },
    {
      "table": "user_notification",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 74702,
        "Damascus": 46602,
        "Homs": 110963,
        "Latakia": 23075,
        "AlWadi": 18714
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 26573
    },
    {
      "table": "version",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1,
        "Damascus": 1,
        "Homs": 1,
        "Latakia": 1,
        "AlWadi": 1
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "visit",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1291,
        "Damascus": 501,
        "Homs": 1431,
        "Latakia": 67,
        "AlWadi": 33
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 1010
    },
    {
      "table": "visit_schedule_temp",
      "cities": [
        "Homs"
      ],
      "counts": {
        "Homs": 343
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "visit_user",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 1948,
        "Damascus": 975,
        "Homs": 1477,
        "Latakia": 179,
        "AlWadi": 33
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 743
    },
    {
      "table": "visitanswers",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 43,
        "Damascus": 43,
        "Homs": 43,
        "Latakia": 43,
        "AlWadi": 43
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "visitquestions",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 20,
        "Damascus": 20,
        "Homs": 20,
        "Latakia": 20,
        "AlWadi": 20
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "work",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 111,
        "Damascus": 123,
        "Homs": 110,
        "Latakia": 136,
        "AlWadi": 101
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 36
    },
    {
      "table": "work_exp",
      "cities": [
        "Damascus"
      ],
      "counts": {
        "Damascus": 2371
      },
      "nonempty_equal_groups": 0,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    },
    {
      "table": "worktype",
      "cities": [
        "Aleppo",
        "Damascus",
        "Homs",
        "Latakia",
        "AlWadi"
      ],
      "counts": {
        "Aleppo": 5,
        "Damascus": 5,
        "Homs": 5,
        "Latakia": 5,
        "AlWadi": 5
      },
      "nonempty_equal_groups": 1,
      "empty_equal_groups": 0,
      "max_same_id_conflicts": 0
    }
  ],
  "city_pair_aggregate_conflicts": {
    "aganswer": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 53,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "age": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "agquestion": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 14,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "aid": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "aidperiod": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "aidsource": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 25,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 25,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 26,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 26,
        "same_id_conflicting_payload": 8,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 25,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 25,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "asset": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "assetbenefits": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "assettype": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "backgrounds": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "career": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 273,
        "same_id_conflicting_payload": 10,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 278,
        "same_id_conflicting_payload": 11,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 284,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 278,
        "same_id_conflicting_payload": 11,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 278,
        "same_id_conflicting_payload": 5,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 276,
        "same_id_conflicting_payload": 6,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 278,
        "same_id_conflicting_payload": 5,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 281,
        "same_id_conflicting_payload": 7,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 289,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 281,
        "same_id_conflicting_payload": 7,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "category": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 6,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "checklist": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2210,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2504,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 552,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 427,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2177,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 553,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 427,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 553,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 427,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 351,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "checklist_member": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 35,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 25,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 31,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 15,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 23,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 5,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 21,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "checklist_opinion": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "checklist_position": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 14,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 13,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 13,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 13,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 5,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 26,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "course": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "databases": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "dept": [],
    "donor": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 3,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 3,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 9,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "donorgroup": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 21,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 41,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 34,
        "same_id_conflicting_payload": 6,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 21,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 21,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 38,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 11,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "education": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 93,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 89,
        "same_id_conflicting_payload": 7,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 94,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 89,
        "same_id_conflicting_payload": 7,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 89,
        "same_id_conflicting_payload": 81,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 93,
        "same_id_conflicting_payload": 9,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 89,
        "same_id_conflicting_payload": 81,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 89,
        "same_id_conflicting_payload": 15,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 171,
        "same_id_conflicting_payload": 7,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 89,
        "same_id_conflicting_payload": 15,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "educationlevel": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "educationtype": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "exefile": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "family": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2198,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2784,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 541,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 609,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2052,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 587,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 239,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 489,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 490,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "file": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "fundtype": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "goal": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 15,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "illness": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "image": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 8953,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 8842,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5696,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 9282,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5635,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5512,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2893,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "imagetype": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 12,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "incomesource": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "language": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "level": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "loan": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 481,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 950,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 169,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 153,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 512,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 196,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 179,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 159,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": false,
        "whole_table_equal": false
      }
    ],
    "log": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 106047,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 28676,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "loss": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "manualfile": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "maritalstatus": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "material": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1817,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2450,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2948,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 673,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1811,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2112,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 834,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2805,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 743,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 810,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "meanswer": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 104,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "medicalneeds": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "meprofitmarks": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "mequestion": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 42,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "meresultranges": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 4,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "mevisit": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1144,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2606,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 27,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1459,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 28,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 28,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "mevisit_answer": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16524,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 39779,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 410,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 19,
        "same_id_conflicting_payload": 25025,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 438,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 19,
        "same_id_conflicting_payload": 407,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "mevisit_user": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1235,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1802,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1850,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 37,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 36,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "microproject": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2363,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2974,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 593,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 650,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2361,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 593,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 650,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 593,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 650,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 587,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "microproject_ag_answer": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "microproject_powersource": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 78,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 160,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 347,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 121,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 62,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 77,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 69,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 160,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 84,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 120,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "microproject_rehab": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "microproject_rehab_answer": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "microproject_risk": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 3,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 3,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 20,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 9,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "microproject_score": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 8129,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 6568,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1564,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4674,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 73,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 360,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 940,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "microproject_state": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 3971,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5798,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2057,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1425,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5608,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1579,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1240,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1619,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1367,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "microprojectsubtype": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "microprojecttype": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "militarystate": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "office_monitoring": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 493,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1176,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 66,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 33,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 500,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 66,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 67,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 33,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "parish": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "payment": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 7803,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 19392,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1514,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1085,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 7824,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1647,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1612,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 990,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10308,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 13106,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2197,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2896,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10254,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2358,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1870,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2311,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2544,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": false,
        "whole_table_equal": false
      }
    ],
    "person_aidsource": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 15,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 78,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 40,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 35,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 15,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 15,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 33,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 28,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 35,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_asset": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 497,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 750,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 197,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 684,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 311,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 197,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 435,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 181,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 439,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 155,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_checklistmembers": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10570,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 9403,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4435,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1402,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 12974,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4347,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2860,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4167,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2897,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": false,
        "whole_table_equal": false
      }
    ],
    "person_course": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 125,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 145,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 64,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 18,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 103,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 46,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 37,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 47,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_education": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1202,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1664,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 345,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 373,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1301,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 418,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 92,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 414,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 328,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_family": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10294,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 13076,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2065,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2896,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10441,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 2388,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2012,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2497,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 2542,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_goals": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 297,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 379,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 426,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 343,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 463,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 498,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 403,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 843,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 480,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 538,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_help": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 745,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 720,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 66,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 111,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 749,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 124,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 123,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 123,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 112,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 71,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_illness": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 571,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 572,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 263,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 286,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 697,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 266,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 285,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 247,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 270,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 153,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_loss": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 394,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 409,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 111,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 67,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 412,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 38,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 77,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 28,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 108,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_microproject": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2144,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2766,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 515,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 636,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2256,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 593,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 236,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 547,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 559,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_powersource": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 110,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 506,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 322,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 278,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 19,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 23,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 36,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 470,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 434,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 287,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "person_workexp": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5293,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 6756,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1113,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1555,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5070,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1283,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 447,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1197,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1267,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "personstate": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "powersource": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 7,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "priest": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 57,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 24,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 27,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 47,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 50,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 59,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_answer": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_attendee": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 3,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_brand": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_challenge": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_competition": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_equipment": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_minute": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_option_answer": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 14,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_participant": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 6,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_meeting_season": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "profession_option": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 44,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "profession_question": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 22,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "profession_section": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "purchasing": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "purchasingcategory": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "rehabanswer": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 56,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "rehabquestion": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "rehabvisit": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "relation": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 27,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "resultstable": [
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "risk": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 3,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "role": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 8,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "score": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 10,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "scorevalue": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 30,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "specialcase": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 103,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 100,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 19,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 96,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 19,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 19,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "specialcasetype": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "state": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 11,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "statereason": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 23,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 23,
        "same_id_conflicting_payload": 2,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 25,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 25,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 24,
        "same_id_conflicting_payload": 1,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "street": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 19,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 60,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 8,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 29,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 29,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 23,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 18,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 2,
        "same_id_conflicting_payload": 12,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 23,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "subcategory": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 17,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "t1": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": false,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "task": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 39,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "task_microproject": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 18584,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 37410,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 7415,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10790,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 25788,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 12137,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 12137,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 8292,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "task_user": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 5,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 36,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 10,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 52,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 24,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 56,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 24,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 62,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 77,
        "same_id_conflicting_payload": 28,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 62,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "temp_gender": [],
    "user": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 24,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 38,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 14,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 38,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 17,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 9,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 41,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 11,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "user_notification": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 657,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 20504,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1617,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1582,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 26573,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 8515,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 6875,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 7366,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 4860,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 16150,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "version": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "visit": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 194,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 1010,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 33,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 500,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 65,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 65,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 33,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "visit_schedule_temp": [],
    "visit_user": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 371,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 630,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 65,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 19,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 743,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 107,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 93,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 1,
        "same_id_conflicting_payload": 27,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 0,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "visitanswers": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 43,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "visitquestions": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 20,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ],
    "work": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 84,
        "same_id_conflicting_payload": 27,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 80,
        "same_id_conflicting_payload": 28,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 111,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 80,
        "same_id_conflicting_payload": 19,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 80,
        "same_id_conflicting_payload": 30,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 84,
        "same_id_conflicting_payload": 36,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 80,
        "same_id_conflicting_payload": 21,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 80,
        "same_id_conflicting_payload": 28,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 94,
        "same_id_conflicting_payload": 7,
        "column_contract_equal": true,
        "whole_table_equal": false
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 80,
        "same_id_conflicting_payload": 19,
        "column_contract_equal": true,
        "whole_table_equal": false
      }
    ],
    "work_exp": [],
    "worktype": [
      {
        "cities": [
          "Aleppo",
          "Damascus"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Aleppo",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Homs"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Damascus",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "Latakia"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Homs",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      },
      {
        "cities": [
          "Latakia",
          "AlWadi"
        ],
        "same_id_exact_payload": 5,
        "same_id_conflicting_payload": 0,
        "column_contract_equal": true,
        "whole_table_equal": true
      }
    ]
  },
  "elapsed_seconds": 35.67
}
```

Physical approach proposed for review: retain every immutable per-source occurrence and introduce one shared dictionary row per approved complete-table equality group, plus explicit source/city membership and original-key-to-shared-row mappings. Canonical child links may target shared lookup identity only when their whole parent closure is compatible. Mutable tasks retain city membership and per-city capability checks; shared content must not imply shared completion/assignment state. No global uniqueness on legacy IDs, no sharing of arbitrary matching subsets, and no loss of original source/snapshot provenance.

Reproduce with Python 3: run audit-shared-data.py. It reads only the five frozen archives and committed schema metadata, validates every row count and digest, and overwrites only this aggregate report. Elapsed seconds: 35.67.
