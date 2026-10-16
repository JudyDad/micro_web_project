# P1.2 exhaustive typed retention table breakdown

DRAFT — schema confirmation and restored profiling pending. Not executable DDL.

Read p1-2-target-physical-schema.md for the common columns, indexes, constraint policy and limits. Every table below is retained in the same proposed operational MySQL database as a source-specific compatibility table. No table/column is discarded. This appendix is not approval of a normalized application schema.

## Aleppo.aganswer → legacy_aleppo_aganswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.aganswer.fk1.

## Aleppo.age → legacy_aleppo_age

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.agquestion → legacy_aleppo_agquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.aid → legacy_aleppo_aid

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.aidperiod → legacy_aleppo_aidperiod

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.aidsource → legacy_aleppo_aidsource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.asset → legacy_aleppo_asset

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.assetbenefits → legacy_aleppo_assetbenefits

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.assettype → legacy_aleppo_assettype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.backgrounds → legacy_aleppo_backgrounds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.career → legacy_aleppo_career

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.career.fk1.

## Aleppo.category → legacy_aleppo_category

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.checklist → legacy_aleppo_checklist

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int NOT NULL` |
| `HasHomeVisit` | `int NOT NULL DEFAULT '-1'` |
| `HVisit_Date` | `date DEFAULT NULL` |
| `HVisit_Time` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `HVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `HVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `HVisit_Note` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `HasProjectVisit` | `int NOT NULL DEFAULT '-1'` |
| `PVisit_Date` | `date DEFAULT NULL` |
| `PVisit_Time` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `PVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `PVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `PVisit_Note` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `VisitsReport` | `text COLLATE utf8mb4_bin NOT NULL` |
| `NeedProTest` | `int NOT NULL DEFAULT '-1'` |
| `NeedProTest_Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `HasProTest` | `int NOT NULL DEFAULT '-1'` |
| `ProTest_Date` | `date DEFAULT NULL` |
| `ProTest_Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `ProTest_User_ID` | `int DEFAULT NULL` |
| `ProTest_Result` | `text COLLATE utf8mb4_bin NOT NULL` |
| `HasProTrain` | `int NOT NULL DEFAULT '-1'` |
| `ProTrain_Trainer` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Place` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_BDate` | `date DEFAULT NULL` |
| `ProTrain_EDate` | `date DEFAULT NULL` |
| `ProTrain_Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Result` | `text COLLATE utf8mb4_bin NOT NULL` |
| `HasMngTrain` | `int NOT NULL DEFAULT '-1'` |
| `MngTrain_Trainer` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Place` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Date` | `date DEFAULT NULL` |
| `MngTrain_Result` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `TeamDate` | `date DEFAULT NULL` |
| `CommityDate` | `date DEFAULT NULL` |
| `RejectMsg` | `int NOT NULL DEFAULT '-1'` |
| `RejectMsgDate` | `date DEFAULT NULL` |
| `RejectMsgSender_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4199 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.checklist.fk1, Aleppo.checklist.fk2, Aleppo.checklist.fk3, Aleppo.checklist.fk4, Aleppo.checklist.fk5, Aleppo.checklist.fk6, Aleppo.checklist.fk7.

## Aleppo.checklist_member → legacy_aleppo_checklist_member

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.checklist_member.fk1.

## Aleppo.checklist_opinion → legacy_aleppo_checklist_opinion

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.checklist_position → legacy_aleppo_checklist_position

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.course → legacy_aleppo_course

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.databases → legacy_aleppo_databases

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.donor → legacy_aleppo_donor

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.donorgroup → legacy_aleppo_donorgroup

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.donorgroup.fk1.

## Aleppo.education → legacy_aleppo_education

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.educationlevel → legacy_aleppo_educationlevel

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.educationtype → legacy_aleppo_educationtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.exefile → legacy_aleppo_exefile

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ExeF_ID`.
Source indexes: `PRIMARY KEY (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.exefile.fk1.

## Aleppo.family → legacy_aleppo_family

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `F_ID`.
Source indexes: `PRIMARY KEY (`F_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4031 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: none declared.

## Aleppo.file → legacy_aleppo_file

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `File_ID`.
Source indexes: `PRIMARY KEY (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.file.fk1.

## Aleppo.fundtype → legacy_aleppo_fundtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.goal → legacy_aleppo_goal

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.illness → legacy_aleppo_illness

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.image → legacy_aleppo_image

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `Image_ID`.
Source indexes: `PRIMARY KEY (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10175 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.image.fk1, Aleppo.image.fk2, Aleppo.image.fk3.

## Aleppo.imagetype → legacy_aleppo_imagetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.incomesource → legacy_aleppo_incomesource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.language → legacy_aleppo_language

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `L_ID`.
Source indexes: `PRIMARY KEY (`L_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.level → legacy_aleppo_level

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Level_ID`.
Source indexes: `PRIMARY KEY (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.loan → legacy_aleppo_loan

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Loan_ID` | `int NOT NULL` |
| `Currency` | `enum('USD','SP') COLLATE utf8mb4_bin NOT NULL` |
| `ExchangeRate` | `decimal(12,2) NOT NULL DEFAULT '1.00'` |
| `Loan_Amount` | `decimal(12,2) NOT NULL DEFAULT '0.00'` |
| `Loan_DateTaken` | `date DEFAULT NULL` |
| `Loan_PaymentsCount` | `float DEFAULT NULL` |
| `Loan_Rate` | `float DEFAULT NULL` |
| `Loan_ReturnedAmount` | `float NOT NULL DEFAULT '0'` |
| `Loan_PaymentsAmount` | `float DEFAULT NULL` |
| `Loan_Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ReceiptID` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `Loan_ID`.
Source indexes: `PRIMARY KEY (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1086 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;`.
Relationship mappings: Aleppo.loan.fk1.

## Aleppo.log → legacy_aleppo_log

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Source PK components: `Log_ID`.
Source indexes: `PRIMARY KEY (`Log_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=575520 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.loss → legacy_aleppo_loss

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.manualfile → legacy_aleppo_manualfile

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.maritalstatus → legacy_aleppo_maritalstatus

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.material → legacy_aleppo_material

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.material.fk1.

## Aleppo.meanswer → legacy_aleppo_meanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.meanswer.fk1.

## Aleppo.medicalneeds → legacy_aleppo_medicalneeds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.meprofitmarks → legacy_aleppo_meprofitmarks

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.mequestion → legacy_aleppo_mequestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.meresultranges → legacy_aleppo_meresultranges

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.mevisit → legacy_aleppo_mevisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Number` | `varchar(2) COLLATE utf8mb4_bin NOT NULL` |
| `Kind` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Profit` | `double NOT NULL` |
| `Ans1` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Result` | `int NOT NULL` |
| `Created_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3310 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.mevisit.fk1.

## Aleppo.mevisit_answer → legacy_aleppo_mevisit_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=99731 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.mevisit_answer.fk1, Aleppo.mevisit_answer.fk2, Aleppo.mevisit_answer.fk3.

## Aleppo.mevisit_user → legacy_aleppo_mevisit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6912 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.mevisit_user.fk1, Aleppo.mevisit_user.fk2.

## Aleppo.microproject → legacy_aleppo_microproject

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MP_ID` | `int NOT NULL` |
| `MP_DateOfRequest` | `date DEFAULT NULL` |
| `MP_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `MP_RequestedAmount` | `double NOT NULL` |
| `MP_FundType_ID` | `int DEFAULT '1'` |
| `MP_Type_ID` | `int DEFAULT NULL` |
| `MP_SubType_ID` | `int DEFAULT NULL` |
| `Category_ID` | `int DEFAULT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `Career_ID` | `int DEFAULT NULL` |
| `MP_Speciality` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `MP_Profit_Description` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind_Note` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceType_ID` | `int DEFAULT NULL` |
| `MP_PlaceAddress` | `text COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceNote` | `text COLLATE utf8mb4_bin NOT NULL` |
| `MP_AmperNumber` | `int NOT NULL DEFAULT '0'` |
| `MP_AmperType` | `int NOT NULL DEFAULT '0'` |
| `MP_Agreement1` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement2` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement3` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement4` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement5` | `int NOT NULL DEFAULT '-1'` |
| `MP_Note` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `MP_Donor` | `int DEFAULT NULL` |
| `DonorGroup_ID` | `int DEFAULT NULL` |
| `Partnership` | `int NOT NULL DEFAULT '0'` |
| `IsContentUpdated` | `int NOT NULL DEFAULT '0'` |
| `MP_Street_ID` | `int DEFAULT NULL` |
| `MP_AddressAfterFund` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `MP_PaperUser_ID` | `int DEFAULT NULL` |
| `MP_ProgramUser_ID` | `int DEFAULT NULL` |
| `MP_EditedByUser_ID` | `int DEFAULT NULL` |
| `ApplicationState` | `varchar(16) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `MP_ID`.
Source indexes: `PRIMARY KEY (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_MP_SubCategory` (`SubCategory_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3940 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microproject.fk1, Aleppo.microproject.fk2, Aleppo.microproject.fk3, Aleppo.microproject.fk4, Aleppo.microproject.fk5, Aleppo.microproject.fk6, Aleppo.microproject.fk7, Aleppo.microproject.fk8, Aleppo.microproject.fk9, Aleppo.microproject.fk10, Aleppo.microproject.fk11, Aleppo.microproject.fk12, Aleppo.microproject.fk13.

## Aleppo.microproject_ag_answer → legacy_aleppo_microproject_ag_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Source PK components: `MicroProject_ID`, `Question_ID`, `Answer_ID`.
Source indexes: `PRIMARY KEY (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microproject_ag_answer.fk1.

## Aleppo.microproject_powersource → legacy_aleppo_microproject_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2083 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microproject_powersource.fk1, Aleppo.microproject_powersource.fk2.

## Aleppo.microproject_rehab → legacy_aleppo_microproject_rehab

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microproject_rehab.fk1.

## Aleppo.microproject_rehab_answer → legacy_aleppo_microproject_rehab_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microproject_rehab_answer.fk1, Aleppo.microproject_rehab_answer.fk2, Aleppo.microproject_rehab_answer.fk3.

## Aleppo.microproject_risk → legacy_aleppo_microproject_risk

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microproject_risk.fk1, Aleppo.microproject_risk.fk2.

## Aleppo.microproject_score → legacy_aleppo_microproject_score

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=75374 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microproject_score.fk1, Aleppo.microproject_score.fk2.

## Aleppo.microproject_state → legacy_aleppo_microproject_state

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=13203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microproject_state.fk1, Aleppo.microproject_state.fk2, Aleppo.microproject_state.fk3.

## Aleppo.microprojectsubtype → legacy_aleppo_microprojectsubtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.microprojectsubtype.fk1.

## Aleppo.microprojecttype → legacy_aleppo_microprojecttype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.militarystate → legacy_aleppo_militarystate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.office_monitoring → legacy_aleppo_office_monitoring

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text COLLATE utf8mb4_bin NOT NULL` |
| `OtherComments` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.office_monitoring.fk1.

## Aleppo.parish → legacy_aleppo_parish

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.payment → legacy_aleppo_payment

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Pay_ID`.
Source indexes: `PRIMARY KEY (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21157 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: Aleppo.payment.fk1.

## Aleppo.person → legacy_aleppo_person

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `P_ID` | `int NOT NULL` |
| `P_FirstName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `P_LastName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `P_FatherName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `P_MotherName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `P_FullName_en` | `mediumtext COLLATE utf8mb4_bin` |
| `P_DOB` | `date DEFAULT NULL` |
| `P_MilitaryService` | `varchar(200) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryService_Date` | `date DEFAULT NULL` |
| `P_MilitaryService_Note` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryPeriod` | `int DEFAULT NULL` |
| `P_IsMilitaryInWar` | `int DEFAULT '-1'` |
| `P_Mobile` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `P_Mobile2` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_HomeTel` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Street_ID` | `int DEFAULT NULL` |
| `P_HomeAddress` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `P_NationalNumber` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_RegistrationPlace` | `varchar(200) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Sex` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `P_MaritalStatus_ID` | `int DEFAULT NULL` |
| `P_NumAtHome` | `int NOT NULL` |
| `P_NumOfMembers` | `int NOT NULL` |
| `IsProjectOwner` | `varchar(10) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_IntermidiarySide` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `P_HomeType_ID` | `int DEFAULT NULL` |
| `P_HomeState_Note` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `P_MaristesCourse` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `P_Parish_ID` | `int DEFAULT NULL` |
| `P_Priest_ID` | `int DEFAULT NULL` |
| `P_Priest2_ID` | `int DEFAULT NULL` |
| `P_State_ID` | `int DEFAULT '1'` |

Source PK components: `P_ID`.
Source indexes: `PRIMARY KEY (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=15790 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person.fk1, Aleppo.person.fk2, Aleppo.person.fk3, Aleppo.person.fk4, Aleppo.person.fk5, Aleppo.person.fk6, Aleppo.person.fk7.

## Aleppo.person_aidsource → legacy_aleppo_person_aidsource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `Person_AidSource1` (`AidSource_ID`)`; `KEY `Person_AidSource2` (`Person_ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_aidsource.fk1, Aleppo.person_aidsource.fk2, Aleppo.person_aidsource.fk3.

## Aleppo.person_asset → legacy_aleppo_person_asset

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1356 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_asset.fk1, Aleppo.person_asset.fk2, Aleppo.person_asset.fk3, Aleppo.person_asset.fk4.

## Aleppo.person_checklistmembers → legacy_aleppo_person_checklistmembers

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Member_ID` | `int DEFAULT NULL` |
| `Opinion` | `int NOT NULL` |
| `Notes` | `text COLLATE utf8mb4_bin NOT NULL` |
| `NameNotes` | `varchar(400) COLLATE utf8mb4_bin NOT NULL` |
| `RoleNotes` | `varchar(500) COLLATE utf8mb4_bin NOT NULL` |
| `Location` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10920 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_checklistmembers.fk1, Aleppo.person_checklistmembers.fk2.

## Aleppo.person_course → legacy_aleppo_person_course

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=898 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_course.fk1, Aleppo.person_course.fk2.

## Aleppo.person_education → legacy_aleppo_person_education

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2353 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_education.fk1, Aleppo.person_education.fk2, Aleppo.person_education.fk3.

## Aleppo.person_family → legacy_aleppo_person_family

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Family_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Relation_ID` | `int DEFAULT NULL` |
| `EducationLevel_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `HasWork` | `int DEFAULT '-1'` |
| `P_Provider_ID` | `int DEFAULT NULL` |
| `Work_Name` | `longtext COLLATE utf8mb4_bin` |
| `PF_ID` | `int NOT NULL` |

Source PK components: `PF_ID`.
Source indexes: `PRIMARY KEY (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=15837 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_family.fk1, Aleppo.person_family.fk2, Aleppo.person_family.fk3, Aleppo.person_family.fk4, Aleppo.person_family.fk5, Aleppo.person_family.fk6.

## Aleppo.person_goals → legacy_aleppo_person_goals

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=641 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_goals.fk1, Aleppo.person_goals.fk2.

## Aleppo.person_help → legacy_aleppo_person_help

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2241 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_help.fk1, Aleppo.person_help.fk2, Aleppo.person_help.fk3, Aleppo.person_help.fk4.

## Aleppo.person_illness → legacy_aleppo_person_illness

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Illness_ID` | `int DEFAULT NULL` |
| `Has_MedicalAid` | `int NOT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Donor` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `AidPeriod_ID` | `int DEFAULT '0'` |
| `MedicalNeeds_ID` | `int DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=601 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_illness.fk1, Aleppo.person_illness.fk2, Aleppo.person_illness.fk3, Aleppo.person_illness.fk4, Aleppo.person_illness.fk5.

## Aleppo.person_loss → legacy_aleppo_person_loss

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1563 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_loss.fk1, Aleppo.person_loss.fk2.

## Aleppo.person_microproject → legacy_aleppo_person_microproject

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `PMP_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PersonType` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Loss` | `longtext COLLATE utf8mb4_bin` |
| `P_Goal` | `longtext COLLATE utf8mb4_bin` |
| `P_GoalHow` | `longtext COLLATE utf8mb4_bin` |
| `P_OtherNotes` | `longtext COLLATE utf8mb4_bin` |
| `P_HasWork` | `int NOT NULL DEFAULT '-1'` |
| `P_WorkYears` | `mediumtext COLLATE utf8mb4_bin` |
| `P_IsLivingWithFamily` | `int NOT NULL DEFAULT '-1'` |
| `P_LiveWithFamily_Note` | `mediumtext COLLATE utf8mb4_bin` |
| `P_HasEducation` | `int NOT NULL DEFAULT '-1'` |
| `P_HasCourses` | `int NOT NULL DEFAULT '-1'` |
| `P_HasLoss` | `int NOT NULL DEFAULT '-1'` |
| `P_HasIllness` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAssets` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAidSource` | `int NOT NULL DEFAULT '-1'` |
| `P_HasHelp` | `int NOT NULL DEFAULT '-1'` |
| `P_HasDebts` | `int NOT NULL DEFAULT '-1'` |
| `P_DebtsAmount` | `text COLLATE utf8mb4_bin` |
| `P_DebtsNote` | `text COLLATE utf8mb4_bin` |
| `P_AidSourceNote` | `text COLLATE utf8mb4_bin` |

Source PK components: `PMP_ID`.
Source indexes: `PRIMARY KEY (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4253 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_microproject.fk1, Aleppo.person_microproject.fk2.

## Aleppo.person_powersource → legacy_aleppo_person_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2027 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_powersource.fk1, Aleppo.person_powersource.fk2.

## Aleppo.person_workexp → legacy_aleppo_person_workexp

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Work_ID` | `int DEFAULT NULL` |
| `W_Description` | `text COLLATE utf8mb4_bin` |
| `W_WorkType_ID` | `int DEFAULT NULL` |
| `W_BeginDate` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `W_EndDate` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `W_Place` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `W_PlaceState` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `W_Status` | `char(50) COLLATE utf8mb4_bin NOT NULL` |
| `W_Contact` | `longtext COLLATE utf8mb4_bin` |
| `W_CauseOfLoseDescription` | `longtext COLLATE utf8mb4_bin` |
| `W_Salary` | `double DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8953 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.person_workexp.fk1, Aleppo.person_workexp.fk2, Aleppo.person_workexp.fk3, Aleppo.person_workexp.fk4.

## Aleppo.personstate → legacy_aleppo_personstate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.powersource → legacy_aleppo_powersource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.priest → legacy_aleppo_priest

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Priest_ID`.
Source indexes: `PRIMARY KEY (`Priest_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.profession_meeting → legacy_aleppo_profession_meeting

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Career_ID` | `int NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `MeetingDate` | `date NOT NULL` |
| `Place` | `varchar(200) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `InvitedCount` | `smallint NOT NULL DEFAULT '0'` |
| `AttendanceCount` | `smallint NOT NULL DEFAULT '0'` |
| `AbsenceCount` | `smallint NOT NULL DEFAULT '0'` |
| `PaperRegistrar_UserID` | `int DEFAULT NULL` |
| `ProgramRegistrar_UserID` | `int NOT NULL` |
| `Created_At` | `datetime NOT NULL` |
| `Edited_By_UserID` | `int DEFAULT NULL` |
| `Updated_At` | `datetime DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting.fk1, Aleppo.profession_meeting.fk2, Aleppo.profession_meeting.fk3, Aleppo.profession_meeting.fk4, Aleppo.profession_meeting.fk5.

## Aleppo.profession_meeting_answer → legacy_aleppo_profession_meeting_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_answer.fk1, Aleppo.profession_meeting_answer.fk2.

## Aleppo.profession_meeting_attendee → legacy_aleppo_profession_meeting_attendee

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_attendee.fk1, Aleppo.profession_meeting_attendee.fk2, Aleppo.profession_meeting_attendee.fk3.

## Aleppo.profession_meeting_brand → legacy_aleppo_profession_meeting_brand

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_brand.fk1.

## Aleppo.profession_meeting_challenge → legacy_aleppo_profession_meeting_challenge

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_challenge.fk1.

## Aleppo.profession_meeting_competition → legacy_aleppo_profession_meeting_competition

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_competition.fk1.

## Aleppo.profession_meeting_equipment → legacy_aleppo_profession_meeting_equipment

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_equipment.fk1.

## Aleppo.profession_meeting_minute → legacy_aleppo_profession_meeting_minute

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text COLLATE utf8mb3_unicode_ci` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_minute.fk1, Aleppo.profession_meeting_minute.fk2.

## Aleppo.profession_meeting_option_answer → legacy_aleppo_profession_meeting_option_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_option_answer.fk1, Aleppo.profession_meeting_option_answer.fk2.

## Aleppo.profession_meeting_participant → legacy_aleppo_profession_meeting_participant

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_Name` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Notes` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_participant.fk1, Aleppo.profession_meeting_participant.fk2, Aleppo.profession_meeting_participant.fk3, Aleppo.profession_meeting_participant.fk4.

## Aleppo.profession_meeting_season → legacy_aleppo_profession_meeting_season

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_meeting_season.fk1.

## Aleppo.profession_option → legacy_aleppo_profession_option

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_option.fk1.

## Aleppo.profession_question → legacy_aleppo_profession_question

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Aleppo.profession_question.fk1.

## Aleppo.profession_section → legacy_aleppo_profession_section

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: none declared.

## Aleppo.purchasing → legacy_aleppo_purchasing

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `ItemName` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `Brand` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `ItemCategory_ID` | `int DEFAULT NULL` |
| `Quantity` | `int NOT NULL` |
| `Price` | `decimal(12,2) NOT NULL` |
| `Currency` | `enum('USD','SYP') COLLATE utf8mb4_bin NOT NULL` |
| `InvoiceDate` | `date DEFAULT NULL` |
| `InvoiceNumber` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `TraderName` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.purchasing.fk1, Aleppo.purchasing.fk2.

## Aleppo.purchasingcategory → legacy_aleppo_purchasingcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.rehabanswer → legacy_aleppo_rehabanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.rehabanswer.fk1.

## Aleppo.rehabquestion → legacy_aleppo_rehabquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.rehabvisit → legacy_aleppo_rehabvisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans8` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans9` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans10` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans11` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans12` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans13` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans14` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans15` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans8_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans9_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans10_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans11_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans12_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans13_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans14_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans15_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.rehabvisit.fk1.

## Aleppo.relation → legacy_aleppo_relation

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.risk → legacy_aleppo_risk

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.role → legacy_aleppo_role

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Role_ID`.
Source indexes: `PRIMARY KEY (`Role_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.score → legacy_aleppo_score

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.scorevalue → legacy_aleppo_scorevalue

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.scorevalue.fk1.

## Aleppo.specialcase → legacy_aleppo_specialcase

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.specialcase.fk1, Aleppo.specialcase.fk2, Aleppo.specialcase.fk3.

## Aleppo.specialcasetype → legacy_aleppo_specialcasetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.state → legacy_aleppo_state

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.statereason → legacy_aleppo_statereason

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.street → legacy_aleppo_street

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.subcategory → legacy_aleppo_subcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.subcategory.fk1.

## Aleppo.t1 → legacy_aleppo_t1

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.task → legacy_aleppo_task

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `T_ID`.
Source indexes: `PRIMARY KEY (`T_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.task_microproject → legacy_aleppo_task_microproject

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Source PK components: `TMP_ID`.
Source indexes: `PRIMARY KEY (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=59225 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.task_microproject.fk1, Aleppo.task_microproject.fk2.

## Aleppo.task_user → legacy_aleppo_task_user

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `TU_ID`.
Source indexes: `PRIMARY KEY (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=286 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.task_user.fk1, Aleppo.task_user.fk2.

## Aleppo.user → legacy_aleppo_user

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `UserID` | `int NOT NULL` |
| `UserName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Password` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Email` | `varchar(150) COLLATE utf8mb4_bin NOT NULL DEFAULT ''` |
| `UserRoleID` | `int NOT NULL` |
| `IsVisitor` | `int NOT NULL` |
| `IsME` | `int NOT NULL` |
| `IsCommunication` | `int NOT NULL` |
| `IsCashier` | `int NOT NULL DEFAULT '0'` |
| `ProfilePicture` | `mediumblob` |

Source PK components: `UserID`.
Source indexes: `PRIMARY KEY (`UserID`)`; `UNIQUE KEY `UserName` (`UserName`)`; `KEY `FK_User_Role` (`UserRoleID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.user.fk1.

## Aleppo.user_notification → legacy_aleppo_user_notification

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Body` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Seen` | `int NOT NULL` |
| `P_Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Sender_ID` | `int DEFAULT NULL` |
| `Pay_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=227668 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.user_notification.fk1, Aleppo.user_notification.fk2, Aleppo.user_notification.fk3.

## Aleppo.version → legacy_aleppo_version

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.visit → legacy_aleppo_visit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Kind` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Ans1` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `VehicleNumber` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1717 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.visit.fk1.

## Aleppo.visit_user → legacy_aleppo_visit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3525 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.visit_user.fk1, Aleppo.visit_user.fk2.

## Aleppo.visitanswers → legacy_aleppo_visitanswers

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Aleppo.visitanswers.fk1.

## Aleppo.visitquestions → legacy_aleppo_visitquestions

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.work → legacy_aleppo_work

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `W_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Source PK components: `W_ID`.
Source indexes: `PRIMARY KEY (`W_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Aleppo.worktype → legacy_aleppo_worktype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.aganswer → legacy_damascus_aganswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.aganswer.fk1.

## Damascus.age → legacy_damascus_age

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.agquestion → legacy_damascus_agquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.aid → legacy_damascus_aid

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.aidperiod → legacy_damascus_aidperiod

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.aidsource → legacy_damascus_aidsource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.asset → legacy_damascus_asset

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.assetbenefits → legacy_damascus_assetbenefits

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.assettype → legacy_damascus_assettype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.backgrounds → legacy_damascus_backgrounds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.career → legacy_damascus_career

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.career.fk1.

## Damascus.category → legacy_damascus_category

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.checklist → legacy_damascus_checklist

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int NOT NULL` |
| `HasHomeVisit` | `int NOT NULL DEFAULT '-1'` |
| `HVisit_Date` | `date DEFAULT NULL` |
| `HVisit_Time` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `HVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `HVisit_Note` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProjectVisit` | `int NOT NULL DEFAULT '-1'` |
| `PVisit_Date` | `date DEFAULT NULL` |
| `PVisit_Time` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `PVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `PVisit_Note` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `VisitsReport` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `NeedProTest` | `int NOT NULL DEFAULT '-1'` |
| `NeedProTest_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProTest` | `int NOT NULL DEFAULT '-1'` |
| `ProTest_Date` | `date DEFAULT NULL` |
| `ProTest_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTest_User_ID` | `int DEFAULT NULL` |
| `ProTest_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProTrain` | `int NOT NULL DEFAULT '-1'` |
| `ProTrain_Trainer` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Place` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_BDate` | `date DEFAULT NULL` |
| `ProTrain_EDate` | `date DEFAULT NULL` |
| `ProTrain_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasMngTrain` | `int NOT NULL DEFAULT '-1'` |
| `MngTrain_Trainer` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Place` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Date` | `date DEFAULT NULL` |
| `MngTrain_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `TeamDate` | `date DEFAULT NULL` |
| `CommityDate` | `date DEFAULT NULL` |
| `RejectMsg` | `int NOT NULL DEFAULT '-1'` |
| `RejectMsgDate` | `date DEFAULT NULL` |
| `RejectMsgSender_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4219 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.checklist.fk1, Damascus.checklist.fk2, Damascus.checklist.fk3, Damascus.checklist.fk4, Damascus.checklist.fk5, Damascus.checklist.fk6, Damascus.checklist.fk7.

## Damascus.checklist_member → legacy_damascus_checklist_member

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.checklist_member.fk1.

## Damascus.checklist_opinion → legacy_damascus_checklist_opinion

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.checklist_position → legacy_damascus_checklist_position

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.course → legacy_damascus_course

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.databases → legacy_damascus_databases

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.dept → legacy_damascus_dept

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `PMP_ID` | `int DEFAULT NULL` |
| `P_HasDebts` | `int DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3;`.
Relationship mappings: none declared.

## Damascus.donor → legacy_damascus_donor

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.donorgroup → legacy_damascus_donorgroup

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.donorgroup.fk1.

## Damascus.education → legacy_damascus_education

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.educationlevel → legacy_damascus_educationlevel

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.educationtype → legacy_damascus_educationtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.exefile → legacy_damascus_exefile

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ExeF_ID`.
Source indexes: `PRIMARY KEY (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.exefile.fk1.

## Damascus.family → legacy_damascus_family

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `F_ID`.
Source indexes: `PRIMARY KEY (`F_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2395 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: none declared.

## Damascus.file → legacy_damascus_file

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `File_ID`.
Source indexes: `PRIMARY KEY (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.file.fk1.

## Damascus.fundtype → legacy_damascus_fundtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.goal → legacy_damascus_goal

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.illness → legacy_damascus_illness

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.image → legacy_damascus_image

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `Image_ID`.
Source indexes: `PRIMARY KEY (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10599 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.image.fk1, Damascus.image.fk2, Damascus.image.fk3.

## Damascus.imagetype → legacy_damascus_imagetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.incomesource → legacy_damascus_incomesource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.language → legacy_damascus_language

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `L_ID`.
Source indexes: `PRIMARY KEY (`L_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.level → legacy_damascus_level

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Level_ID`.
Source indexes: `PRIMARY KEY (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.loan → legacy_damascus_loan

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Loan_ID` | `int NOT NULL` |
| `Currency` | `enum('USD','SP') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExchangeRate` | `decimal(12,2) NOT NULL DEFAULT '1.00'` |
| `Loan_Amount` | `decimal(12,2) NOT NULL DEFAULT '0.00'` |
| `Loan_DateTaken` | `date DEFAULT NULL` |
| `Loan_PaymentsCount` | `float DEFAULT NULL` |
| `Loan_Rate` | `float DEFAULT NULL` |
| `Loan_ReturnedAmount` | `float NOT NULL DEFAULT '0'` |
| `Loan_PaymentsAmount` | `float DEFAULT NULL` |
| `Loan_Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ReceiptID` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `Loan_ID`.
Source indexes: `PRIMARY KEY (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=550 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;`.
Relationship mappings: Damascus.loan.fk1.

## Damascus.log → legacy_damascus_log

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Source PK components: `Log_ID`.
Source indexes: `PRIMARY KEY (`Log_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=361748 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.loss → legacy_damascus_loss

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.manualfile → legacy_damascus_manualfile

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.maritalstatus → legacy_damascus_maritalstatus

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.material → legacy_damascus_material

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2211 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.material.fk1.

## Damascus.meanswer → legacy_damascus_meanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.meanswer.fk1.

## Damascus.medicalneeds → legacy_damascus_medicalneeds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.meprofitmarks → legacy_damascus_meprofitmarks

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.mequestion → legacy_damascus_mequestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.meresultranges → legacy_damascus_meresultranges

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.mevisit → legacy_damascus_mevisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Number` | `varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Kind` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Profit` | `double NOT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Result` | `int NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1472 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.mevisit.fk1.

## Damascus.mevisit_answer → legacy_damascus_mevisit_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=38633 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.mevisit_answer.fk1, Damascus.mevisit_answer.fk2, Damascus.mevisit_answer.fk3.

## Damascus.mevisit_user → legacy_damascus_mevisit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2861 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.mevisit_user.fk1, Damascus.mevisit_user.fk2.

## Damascus.microproject → legacy_damascus_microproject

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MP_ID` | `int NOT NULL` |
| `MP_DateOfRequest` | `date DEFAULT NULL` |
| `MP_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_RequestedAmount` | `double NOT NULL` |
| `MP_FundType_ID` | `int DEFAULT '1'` |
| `MP_Type_ID` | `int DEFAULT NULL` |
| `MP_SubType_ID` | `int DEFAULT NULL` |
| `Category_ID` | `int DEFAULT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `Career_ID` | `int DEFAULT NULL` |
| `MP_Speciality` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_Profit_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind_Note` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceType_ID` | `int DEFAULT NULL` |
| `MP_PlaceAddress` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_AmperNumber` | `int NOT NULL DEFAULT '0'` |
| `MP_AmperType` | `int NOT NULL DEFAULT '0'` |
| `MP_Agreement1` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement2` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement3` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement4` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement5` | `int NOT NULL DEFAULT '-1'` |
| `MP_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_Donor` | `int DEFAULT NULL` |
| `DonorGroup_ID` | `int DEFAULT NULL` |
| `Partnership` | `int NOT NULL DEFAULT '0'` |
| `IsContentUpdated` | `int NOT NULL DEFAULT '0'` |
| `MP_Street_ID` | `int DEFAULT NULL` |
| `MP_AddressAfterFund` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PaperUser_ID` | `int DEFAULT NULL` |
| `MP_ProgramUser_ID` | `int DEFAULT NULL` |
| `MP_EditedByUser_ID` | `int DEFAULT NULL` |
| `ApplicationState` | `varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `MP_ID`.
Source indexes: `PRIMARY KEY (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_MP_SubCategory` (`SubCategory_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2364 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microproject.fk1, Damascus.microproject.fk2, Damascus.microproject.fk3, Damascus.microproject.fk4, Damascus.microproject.fk5, Damascus.microproject.fk6, Damascus.microproject.fk7, Damascus.microproject.fk8, Damascus.microproject.fk9, Damascus.microproject.fk10, Damascus.microproject.fk11, Damascus.microproject.fk12, Damascus.microproject.fk13.

## Damascus.microproject_ag_answer → legacy_damascus_microproject_ag_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Source PK components: `MicroProject_ID`, `Question_ID`, `Answer_ID`.
Source indexes: `PRIMARY KEY (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microproject_ag_answer.fk1.

## Damascus.microproject_powersource → legacy_damascus_microproject_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microproject_powersource.fk1, Damascus.microproject_powersource.fk2.

## Damascus.microproject_rehab → legacy_damascus_microproject_rehab

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microproject_rehab.fk1.

## Damascus.microproject_rehab_answer → legacy_damascus_microproject_rehab_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microproject_rehab_answer.fk1, Damascus.microproject_rehab_answer.fk2, Damascus.microproject_rehab_answer.fk3.

## Damascus.microproject_risk → legacy_damascus_microproject_risk

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microproject_risk.fk1, Damascus.microproject_risk.fk2.

## Damascus.microproject_score → legacy_damascus_microproject_score

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=80893 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microproject_score.fk1, Damascus.microproject_score.fk2.

## Damascus.microproject_state → legacy_damascus_microproject_state

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8559 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microproject_state.fk1, Damascus.microproject_state.fk2, Damascus.microproject_state.fk3.

## Damascus.microprojectsubtype → legacy_damascus_microprojectsubtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.microprojectsubtype.fk1.

## Damascus.microprojecttype → legacy_damascus_microprojecttype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.militarystate → legacy_damascus_militarystate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.office_monitoring → legacy_damascus_office_monitoring

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `OtherComments` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=509 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.office_monitoring.fk1.

## Damascus.parish → legacy_damascus_parish

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.payment → legacy_damascus_payment

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL DEFAULT '0'` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Pay_ID`.
Source indexes: `PRIMARY KEY (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8296 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: Damascus.payment.fk1.

## Damascus.person → legacy_damascus_person

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `P_ID` | `int NOT NULL` |
| `P_FirstName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_LastName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_FatherName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MotherName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_FullName_en` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_DOB` | `date DEFAULT NULL` |
| `P_MilitaryService` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryService_Date` | `date DEFAULT NULL` |
| `P_MilitaryService_Note` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryPeriod` | `int DEFAULT NULL` |
| `P_IsMilitaryInWar` | `int DEFAULT '-1'` |
| `P_Mobile` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_Mobile2` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_HomeTel` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Street_ID` | `int DEFAULT NULL` |
| `P_HomeAddress` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_NationalNumber` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_RegistrationPlace` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Sex` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MaritalStatus_ID` | `int DEFAULT NULL` |
| `P_NumAtHome` | `int NOT NULL` |
| `P_NumOfMembers` | `int NOT NULL` |
| `IsProjectOwner` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_IntermidiarySide` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_HomeType_ID` | `int DEFAULT NULL` |
| `P_HomeState_Note` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MaristesCourse` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_Parish_ID` | `int DEFAULT NULL` |
| `P_Priest_ID` | `int DEFAULT NULL` |
| `P_Priest2_ID` | `int DEFAULT NULL` |
| `P_State_ID` | `int DEFAULT '1'` |

Source PK components: `P_ID`.
Source indexes: `PRIMARY KEY (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11602 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person.fk1, Damascus.person.fk2, Damascus.person.fk3, Damascus.person.fk4, Damascus.person.fk5, Damascus.person.fk6, Damascus.person.fk7.

## Damascus.person_aidsource → legacy_damascus_person_aidsource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `Person_AidSource1` (`AidSource_ID`)`; `KEY `Person_AidSource2` (`Person_ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_aidsource.fk1, Damascus.person_aidsource.fk2, Damascus.person_aidsource.fk3.

## Damascus.person_asset → legacy_damascus_person_asset

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=706 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_asset.fk1, Damascus.person_asset.fk2, Damascus.person_asset.fk3, Damascus.person_asset.fk4.

## Damascus.person_checklistmembers → legacy_damascus_person_checklistmembers

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Member_ID` | `int NOT NULL` |
| `Opinion` | `int NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `NameNotes` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `RoleNotes` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Location` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=18021 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_checklistmembers.fk1, Damascus.person_checklistmembers.fk2.

## Damascus.person_course → legacy_damascus_person_course

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=825 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_course.fk1, Damascus.person_course.fk2.

## Damascus.person_education → legacy_damascus_person_education

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1375 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_education.fk1, Damascus.person_education.fk2, Damascus.person_education.fk3.

## Damascus.person_family → legacy_damascus_person_family

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Family_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Relation_ID` | `int DEFAULT NULL` |
| `EducationLevel_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `HasWork` | `int DEFAULT '-1'` |
| `P_Provider_ID` | `int DEFAULT NULL` |
| `Work_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `PF_ID` | `int NOT NULL` |

Source PK components: `PF_ID`.
Source indexes: `PRIMARY KEY (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11527 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_family.fk1, Damascus.person_family.fk2, Damascus.person_family.fk3, Damascus.person_family.fk4, Damascus.person_family.fk5, Damascus.person_family.fk6.

## Damascus.person_goals → legacy_damascus_person_goals

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=654 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_goals.fk1, Damascus.person_goals.fk2.

## Damascus.person_help → legacy_damascus_person_help

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1344 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_help.fk1, Damascus.person_help.fk2, Damascus.person_help.fk3, Damascus.person_help.fk4.

## Damascus.person_illness → legacy_damascus_person_illness

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Illness_ID` | `int DEFAULT NULL` |
| `Has_MedicalAid` | `int NOT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Donor` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `AidPeriod_ID` | `int DEFAULT '0'` |
| `MedicalNeeds_ID` | `int DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=775 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_illness.fk1, Damascus.person_illness.fk2, Damascus.person_illness.fk3, Damascus.person_illness.fk4, Damascus.person_illness.fk5.

## Damascus.person_loss → legacy_damascus_person_loss

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1647 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_loss.fk1, Damascus.person_loss.fk2.

## Damascus.person_microproject → legacy_damascus_person_microproject

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `PMP_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PersonType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Loss` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_Goal` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_GoalHow` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_OtherNotes` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_HasWork` | `int NOT NULL DEFAULT '-1'` |
| `P_WorkYears` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_IsLivingWithFamily` | `int NOT NULL DEFAULT '-1'` |
| `P_LiveWithFamily_Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_HasEducation` | `int NOT NULL DEFAULT '-1'` |
| `P_HasCourses` | `int NOT NULL DEFAULT '-1'` |
| `P_HasLoss` | `int NOT NULL DEFAULT '-1'` |
| `P_HasIllness` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAssets` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAidSource` | `int NOT NULL DEFAULT '-1'` |
| `P_HasHelp` | `int NOT NULL DEFAULT '-1'` |
| `P_HasDebts` | `int NOT NULL DEFAULT '-1'` |
| `P_DebtsAmount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_DebtsNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_AidSourceNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |

Source PK components: `PMP_ID`.
Source indexes: `PRIMARY KEY (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2425 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_microproject.fk1, Damascus.person_microproject.fk2.

## Damascus.person_powersource → legacy_damascus_person_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_powersource.fk1, Damascus.person_powersource.fk2.

## Damascus.person_workexp → legacy_damascus_person_workexp

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Work_ID` | `int DEFAULT NULL` |
| `W_Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_WorkType_ID` | `int DEFAULT NULL` |
| `W_BeginDate` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_EndDate` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Place` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_PlaceState` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Status` | `char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Contact` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_CauseOfLoseDescription` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_Salary` | `double DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5779 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.person_workexp.fk1, Damascus.person_workexp.fk2, Damascus.person_workexp.fk3, Damascus.person_workexp.fk4.

## Damascus.personstate → legacy_damascus_personstate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.powersource → legacy_damascus_powersource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.priest → legacy_damascus_priest

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Priest_ID`.
Source indexes: `PRIMARY KEY (`Priest_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.profession_meeting → legacy_damascus_profession_meeting

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Career_ID` | `int NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `MeetingDate` | `date NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `InvitedCount` | `smallint NOT NULL DEFAULT '0'` |
| `AttendanceCount` | `smallint NOT NULL DEFAULT '0'` |
| `AbsenceCount` | `smallint NOT NULL DEFAULT '0'` |
| `PaperRegistrar_UserID` | `int DEFAULT NULL` |
| `ProgramRegistrar_UserID` | `int NOT NULL` |
| `Created_At` | `datetime NOT NULL` |
| `Edited_By_UserID` | `int DEFAULT NULL` |
| `Updated_At` | `datetime DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting.fk1, Damascus.profession_meeting.fk2, Damascus.profession_meeting.fk3, Damascus.profession_meeting.fk4, Damascus.profession_meeting.fk5.

## Damascus.profession_meeting_answer → legacy_damascus_profession_meeting_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_answer.fk1, Damascus.profession_meeting_answer.fk2.

## Damascus.profession_meeting_attendee → legacy_damascus_profession_meeting_attendee

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_attendee.fk1, Damascus.profession_meeting_attendee.fk2, Damascus.profession_meeting_attendee.fk3.

## Damascus.profession_meeting_brand → legacy_damascus_profession_meeting_brand

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_brand.fk1.

## Damascus.profession_meeting_challenge → legacy_damascus_profession_meeting_challenge

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_challenge.fk1.

## Damascus.profession_meeting_competition → legacy_damascus_profession_meeting_competition

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_competition.fk1.

## Damascus.profession_meeting_equipment → legacy_damascus_profession_meeting_equipment

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_equipment.fk1.

## Damascus.profession_meeting_minute → legacy_damascus_profession_meeting_minute

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_minute.fk1, Damascus.profession_meeting_minute.fk2.

## Damascus.profession_meeting_option_answer → legacy_damascus_profession_meeting_option_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_option_answer.fk1, Damascus.profession_meeting_option_answer.fk2.

## Damascus.profession_meeting_participant → legacy_damascus_profession_meeting_participant

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Notes` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_participant.fk1, Damascus.profession_meeting_participant.fk2, Damascus.profession_meeting_participant.fk3, Damascus.profession_meeting_participant.fk4.

## Damascus.profession_meeting_season → legacy_damascus_profession_meeting_season

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_meeting_season.fk1.

## Damascus.profession_option → legacy_damascus_profession_option

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_option.fk1.

## Damascus.profession_question → legacy_damascus_profession_question

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Damascus.profession_question.fk1.

## Damascus.profession_section → legacy_damascus_profession_section

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: none declared.

## Damascus.purchasing → legacy_damascus_purchasing

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `ItemName` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Brand` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ItemCategory_ID` | `int DEFAULT NULL` |
| `Quantity` | `int NOT NULL` |
| `Price` | `decimal(12,2) NOT NULL` |
| `Currency` | `enum('USD','SYP') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `InvoiceDate` | `date DEFAULT NULL` |
| `InvoiceNumber` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `TraderName` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.purchasing.fk1, Damascus.purchasing.fk2.

## Damascus.purchasingcategory → legacy_damascus_purchasingcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.rehabanswer → legacy_damascus_rehabanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.rehabanswer.fk1.

## Damascus.rehabquestion → legacy_damascus_rehabquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.rehabvisit → legacy_damascus_rehabvisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans8` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans9` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans10` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans11` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans12` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans13` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans14` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans15` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans8_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans9_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans10_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans11_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans12_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans13_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans14_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans15_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.rehabvisit.fk1.

## Damascus.relation → legacy_damascus_relation

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.risk → legacy_damascus_risk

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.role → legacy_damascus_role

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Role_ID`.
Source indexes: `PRIMARY KEY (`Role_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.score → legacy_damascus_score

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.scorevalue → legacy_damascus_scorevalue

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.scorevalue.fk1.

## Damascus.specialcase → legacy_damascus_specialcase

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.specialcase.fk1, Damascus.specialcase.fk2, Damascus.specialcase.fk3.

## Damascus.specialcasetype → legacy_damascus_specialcasetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.state → legacy_damascus_state

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.statereason → legacy_damascus_statereason

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.street → legacy_damascus_street

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.subcategory → legacy_damascus_subcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.subcategory.fk1.

## Damascus.t1 → legacy_damascus_t1

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Person_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.task → legacy_damascus_task

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `T_ID`.
Source indexes: `PRIMARY KEY (`T_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.task_microproject → legacy_damascus_task_microproject

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Source PK components: `TMP_ID`.
Source indexes: `PRIMARY KEY (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=27400 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.task_microproject.fk1, Damascus.task_microproject.fk2.

## Damascus.task_user → legacy_damascus_task_user

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `TU_ID`.
Source indexes: `PRIMARY KEY (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.task_user.fk1, Damascus.task_user.fk2.

## Damascus.temp_gender → legacy_damascus_temp_gender

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `varchar(9) DEFAULT NULL` |
| `sex` | `varchar(5) DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3;`.
Relationship mappings: none declared.

## Damascus.user → legacy_damascus_user

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `UserID` | `int NOT NULL` |
| `UserName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Password` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Email` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT ''` |
| `UserRoleID` | `int NOT NULL` |
| `IsVisitor` | `int NOT NULL` |
| `IsME` | `int NOT NULL` |
| `IsCommunication` | `int NOT NULL` |
| `IsCashier` | `int NOT NULL DEFAULT '0'` |
| `ProfilePicture` | `mediumblob` |

Source PK components: `UserID`.
Source indexes: `PRIMARY KEY (`UserID`)`; `KEY `FK_User_Role` (`UserRoleID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.user.fk1.

## Damascus.user_notification → legacy_damascus_user_notification

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Body` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Seen` | `int NOT NULL` |
| `P_Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Sender_ID` | `int DEFAULT NULL` |
| `Pay_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=82146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.user_notification.fk1, Damascus.user_notification.fk2, Damascus.user_notification.fk3.

## Damascus.version → legacy_damascus_version

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.visit → legacy_damascus_visit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Kind` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `VehicleNumber` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=509 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.visit.fk1.

## Damascus.visit_user → legacy_damascus_visit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1280 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.visit_user.fk1, Damascus.visit_user.fk2.

## Damascus.visitanswers → legacy_damascus_visitanswers

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Damascus.visitanswers.fk1.

## Damascus.visitquestions → legacy_damascus_visitquestions

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.work → legacy_damascus_work

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `W_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Source PK components: `W_ID`.
Source indexes: `PRIMARY KEY (`W_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Damascus.work_exp → legacy_damascus_work_exp

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `PMP_ID` | `int DEFAULT NULL` |
| `P_WorkYears` | `int DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3;`.
Relationship mappings: none declared.

## Damascus.worktype → legacy_damascus_worktype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.aganswer → legacy_homs_aganswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.aganswer.fk1.

## Homs.age → legacy_homs_age

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.agquestion → legacy_homs_agquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.aid → legacy_homs_aid

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.aidperiod → legacy_homs_aidperiod

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.aidsource → legacy_homs_aidsource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.asset → legacy_homs_asset

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.assetbenefits → legacy_homs_assetbenefits

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.assettype → legacy_homs_assettype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.backgrounds → legacy_homs_backgrounds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.career → legacy_homs_career

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.career.fk1.

## Homs.category → legacy_homs_category

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.checklist → legacy_homs_checklist

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int NOT NULL` |
| `HasHomeVisit` | `int NOT NULL DEFAULT '-1'` |
| `HVisit_Date` | `date DEFAULT NULL` |
| `HVisit_Time` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `HVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `HVisit_Note` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProjectVisit` | `int NOT NULL DEFAULT '-1'` |
| `PVisit_Date` | `date DEFAULT NULL` |
| `PVisit_Time` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `PVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `PVisit_Note` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `VisitsReport` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `NeedProTest` | `int NOT NULL DEFAULT '-1'` |
| `NeedProTest_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProTest` | `int NOT NULL DEFAULT '-1'` |
| `ProTest_Date` | `date DEFAULT NULL` |
| `ProTest_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTest_User_ID` | `int DEFAULT NULL` |
| `ProTest_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProTrain` | `int NOT NULL DEFAULT '-1'` |
| `ProTrain_Trainer` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Place` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_BDate` | `date DEFAULT NULL` |
| `ProTrain_EDate` | `date DEFAULT NULL` |
| `ProTrain_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasMngTrain` | `int NOT NULL DEFAULT '-1'` |
| `MngTrain_Trainer` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Place` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Date` | `date DEFAULT NULL` |
| `MngTrain_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `TeamDate` | `date DEFAULT NULL` |
| `CommityDate` | `date DEFAULT NULL` |
| `RejectMsg` | `int NOT NULL DEFAULT '-1'` |
| `RejectMsgDate` | `date DEFAULT NULL` |
| `RejectMsgSender_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4166 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.checklist.fk1, Homs.checklist.fk2, Homs.checklist.fk3, Homs.checklist.fk4, Homs.checklist.fk5, Homs.checklist.fk6, Homs.checklist.fk7.

## Homs.checklist_member → legacy_homs_checklist_member

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.checklist_member.fk1.

## Homs.checklist_opinion → legacy_homs_checklist_opinion

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.checklist_position → legacy_homs_checklist_position

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.course → legacy_homs_course

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.databases → legacy_homs_databases

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.donor → legacy_homs_donor

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.donorgroup → legacy_homs_donorgroup

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.donorgroup.fk1.

## Homs.education → legacy_homs_education

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=219 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.educationlevel → legacy_homs_educationlevel

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.educationtype → legacy_homs_educationtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.exefile → legacy_homs_exefile

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ExeF_ID`.
Source indexes: `PRIMARY KEY (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.exefile.fk1.

## Homs.family → legacy_homs_family

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `F_ID`.
Source indexes: `PRIMARY KEY (`F_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3265 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: none declared.

## Homs.file → legacy_homs_file

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `File_ID`.
Source indexes: `PRIMARY KEY (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.file.fk1.

## Homs.fundtype → legacy_homs_fundtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.goal → legacy_homs_goal

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.illness → legacy_homs_illness

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.image → legacy_homs_image

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `Image_ID`.
Source indexes: `PRIMARY KEY (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21042 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.image.fk1, Homs.image.fk2, Homs.image.fk3.

## Homs.imagetype → legacy_homs_imagetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.incomesource → legacy_homs_incomesource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.language → legacy_homs_language

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `L_ID`.
Source indexes: `PRIMARY KEY (`L_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.level → legacy_homs_level

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Level_ID`.
Source indexes: `PRIMARY KEY (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.loan → legacy_homs_loan

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Loan_ID` | `int NOT NULL` |
| `Currency` | `enum('USD','SP') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExchangeRate` | `decimal(12,2) NOT NULL DEFAULT '1.00'` |
| `Loan_Amount` | `decimal(12,2) NOT NULL DEFAULT '0.00'` |
| `Loan_DateTaken` | `date DEFAULT NULL` |
| `Loan_PaymentsCount` | `float DEFAULT NULL` |
| `Loan_Rate` | `float DEFAULT NULL` |
| `Loan_ReturnedAmount` | `double NOT NULL DEFAULT '0'` |
| `Loan_PaymentsAmount` | `double NOT NULL DEFAULT '0'` |
| `Loan_Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ReceiptID` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `Loan_ID`.
Source indexes: `PRIMARY KEY (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;`.
Relationship mappings: Homs.loan.fk1.

## Homs.log → legacy_homs_log

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Source PK components: `Log_ID`.
Source indexes: `PRIMARY KEY (`Log_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=729581 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.loss → legacy_homs_loss

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.manualfile → legacy_homs_manualfile

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.maritalstatus → legacy_homs_maritalstatus

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.material → legacy_homs_material

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3361 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.material.fk1.

## Homs.meanswer → legacy_homs_meanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.meanswer.fk1.

## Homs.medicalneeds → legacy_homs_medicalneeds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.meprofitmarks → legacy_homs_meprofitmarks

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.mequestion → legacy_homs_mequestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.meresultranges → legacy_homs_meresultranges

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.mevisit → legacy_homs_mevisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Number` | `varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Kind` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Profit` | `double NOT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Result` | `int NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2955 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.mevisit.fk1.

## Homs.mevisit_answer → legacy_homs_mevisit_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=88264 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.mevisit_answer.fk1, Homs.mevisit_answer.fk2, Homs.mevisit_answer.fk3.

## Homs.mevisit_user → legacy_homs_mevisit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4061 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.mevisit_user.fk1, Homs.mevisit_user.fk2.

## Homs.microproject → legacy_homs_microproject

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MP_ID` | `int NOT NULL` |
| `MP_DateOfRequest` | `date DEFAULT NULL` |
| `MP_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_RequestedAmount` | `double NOT NULL` |
| `MP_FundType_ID` | `int DEFAULT '1'` |
| `MP_Type_ID` | `int DEFAULT NULL` |
| `MP_SubType_ID` | `int DEFAULT NULL` |
| `Category_ID` | `int DEFAULT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `Career_ID` | `int DEFAULT NULL` |
| `MP_Speciality` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_Profit_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind_Note` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceType_ID` | `int DEFAULT NULL` |
| `MP_PlaceAddress` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_AmperNumber` | `int NOT NULL DEFAULT '0'` |
| `MP_AmperType` | `int NOT NULL DEFAULT '0'` |
| `MP_Agreement1` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement2` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement3` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement4` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement5` | `int NOT NULL DEFAULT '-1'` |
| `MP_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_Donor` | `int DEFAULT NULL` |
| `DonorGroup_ID` | `int DEFAULT NULL` |
| `Partnership` | `int NOT NULL DEFAULT '0'` |
| `IsContentUpdated` | `int NOT NULL DEFAULT '0'` |
| `MP_Street_ID` | `int DEFAULT NULL` |
| `MP_AddressAfterFund` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PaperUser_ID` | `int DEFAULT NULL` |
| `MP_ProgramUser_ID` | `int DEFAULT NULL` |
| `MP_EditedByUser_ID` | `int DEFAULT NULL` |
| `ApplicationState` | `varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `MP_ID`.
Source indexes: `PRIMARY KEY (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_SubCategory_MicroProject` (`SubCategory_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microproject.fk1, Homs.microproject.fk2, Homs.microproject.fk3, Homs.microproject.fk4, Homs.microproject.fk5, Homs.microproject.fk6, Homs.microproject.fk7, Homs.microproject.fk8, Homs.microproject.fk9, Homs.microproject.fk10, Homs.microproject.fk11, Homs.microproject.fk12, Homs.microproject.fk13.

## Homs.microproject_ag_answer → legacy_homs_microproject_ag_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Source PK components: `MicroProject_ID`, `Question_ID`, `Answer_ID`.
Source indexes: `PRIMARY KEY (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microproject_ag_answer.fk1.

## Homs.microproject_powersource → legacy_homs_microproject_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microproject_powersource.fk1, Homs.microproject_powersource.fk2.

## Homs.microproject_rehab → legacy_homs_microproject_rehab

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microproject_rehab.fk1.

## Homs.microproject_rehab_answer → legacy_homs_microproject_rehab_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microproject_rehab_answer.fk1, Homs.microproject_rehab_answer.fk2, Homs.microproject_rehab_answer.fk3.

## Homs.microproject_risk → legacy_homs_microproject_risk

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1092 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microproject_risk.fk1, Homs.microproject_risk.fk2.

## Homs.microproject_score → legacy_homs_microproject_score

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=94387 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microproject_score.fk1, Homs.microproject_score.fk2.

## Homs.microproject_state → legacy_homs_microproject_state

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10529 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microproject_state.fk1, Homs.microproject_state.fk2, Homs.microproject_state.fk3.

## Homs.microprojectsubtype → legacy_homs_microprojectsubtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.microprojectsubtype.fk1.

## Homs.microprojecttype → legacy_homs_microprojecttype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.militarystate → legacy_homs_militarystate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.office_monitoring → legacy_homs_office_monitoring

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `OtherComments` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1434 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.office_monitoring.fk1.

## Homs.parish → legacy_homs_parish

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.payment → legacy_homs_payment

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL DEFAULT '0'` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Pay_ID`.
Source indexes: `PRIMARY KEY (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21904 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: Homs.payment.fk1.

## Homs.person → legacy_homs_person

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `P_ID` | `int NOT NULL` |
| `P_FirstName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_LastName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_FatherName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MotherName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_FullName_en` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_DOB` | `date DEFAULT NULL` |
| `P_MilitaryService` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryService_Date` | `date DEFAULT NULL` |
| `P_MilitaryService_Note` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryPeriod` | `int DEFAULT NULL` |
| `P_IsMilitaryInWar` | `int DEFAULT '-1'` |
| `P_Mobile` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_Mobile2` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_HomeTel` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Street_ID` | `int DEFAULT NULL` |
| `P_HomeAddress` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_NationalNumber` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_RegistrationPlace` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Sex` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MaritalStatus_ID` | `int DEFAULT NULL` |
| `P_NumAtHome` | `int NOT NULL` |
| `P_NumOfMembers` | `int NOT NULL` |
| `IsProjectOwner` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_IntermidiarySide` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_HomeType_ID` | `int DEFAULT NULL` |
| `P_HomeState_Note` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MaristesCourse` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_Parish_ID` | `int DEFAULT NULL` |
| `P_Priest_ID` | `int DEFAULT NULL` |
| `P_Priest2_ID` | `int DEFAULT NULL` |
| `P_State_ID` | `int DEFAULT '1'` |

Source PK components: `P_ID`.
Source indexes: `PRIMARY KEY (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14690 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person.fk1, Homs.person.fk2, Homs.person.fk3, Homs.person.fk4, Homs.person.fk5, Homs.person.fk6, Homs.person.fk7.

## Homs.person_aidsource → legacy_homs_person_aidsource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `Person_AidSource1` (`AidSource_ID`)`; `KEY `Person_AidSource2` (`Person_ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_aidsource.fk1, Homs.person_aidsource.fk2, Homs.person_aidsource.fk3.

## Homs.person_asset → legacy_homs_person_asset

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1684 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_asset.fk1, Homs.person_asset.fk2, Homs.person_asset.fk3, Homs.person_asset.fk4.

## Homs.person_checklistmembers → legacy_homs_person_checklistmembers

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Member_ID` | `int NOT NULL` |
| `Opinion` | `int NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `NameNotes` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `RoleNotes` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Location` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14854 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_checklistmembers.fk1, Homs.person_checklistmembers.fk2.

## Homs.person_course → legacy_homs_person_course

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=996 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_course.fk1, Homs.person_course.fk2.

## Homs.person_education → legacy_homs_person_education

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1851 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_education.fk1, Homs.person_education.fk2, Homs.person_education.fk3.

## Homs.person_family → legacy_homs_person_family

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Family_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Relation_ID` | `int DEFAULT NULL` |
| `EducationLevel_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `HasWork` | `int DEFAULT '-1'` |
| `P_Provider_ID` | `int DEFAULT NULL` |
| `Work_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `PF_ID` | `int NOT NULL` |

Source PK components: `PF_ID`.
Source indexes: `PRIMARY KEY (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14472 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_family.fk1, Homs.person_family.fk2, Homs.person_family.fk3, Homs.person_family.fk4, Homs.person_family.fk5, Homs.person_family.fk6.

## Homs.person_goals → legacy_homs_person_goals

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2027 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_goals.fk1, Homs.person_goals.fk2.

## Homs.person_help → legacy_homs_person_help

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1402 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_help.fk1, Homs.person_help.fk2, Homs.person_help.fk3, Homs.person_help.fk4.

## Homs.person_illness → legacy_homs_person_illness

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Illness_ID` | `int DEFAULT NULL` |
| `Has_MedicalAid` | `int NOT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Donor` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `AidPeriod_ID` | `int DEFAULT '0'` |
| `MedicalNeeds_ID` | `int DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1039 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_illness.fk1, Homs.person_illness.fk2, Homs.person_illness.fk3, Homs.person_illness.fk4, Homs.person_illness.fk5.

## Homs.person_loss → legacy_homs_person_loss

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1777 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_loss.fk1, Homs.person_loss.fk2.

## Homs.person_microproject → legacy_homs_person_microproject

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `PMP_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PersonType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Loss` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_Goal` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_GoalHow` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_OtherNotes` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_HasWork` | `int NOT NULL DEFAULT '-1'` |
| `P_WorkYears` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_IsLivingWithFamily` | `int NOT NULL DEFAULT '-1'` |
| `P_LiveWithFamily_Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_HasEducation` | `int NOT NULL DEFAULT '-1'` |
| `P_HasCourses` | `int NOT NULL DEFAULT '-1'` |
| `P_HasLoss` | `int NOT NULL DEFAULT '-1'` |
| `P_HasIllness` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAssets` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAidSource` | `int NOT NULL DEFAULT '-1'` |
| `P_HasHelp` | `int NOT NULL DEFAULT '-1'` |
| `P_HasDebts` | `int NOT NULL DEFAULT '-1'` |
| `P_DebtsAmount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_DebtsNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_AidSourceNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |

Source PK components: `PMP_ID`.
Source indexes: `PRIMARY KEY (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_microproject.fk1, Homs.person_microproject.fk2.

## Homs.person_powersource → legacy_homs_person_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_powersource.fk1, Homs.person_powersource.fk2.

## Homs.person_workexp → legacy_homs_person_workexp

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Work_ID` | `int DEFAULT NULL` |
| `W_Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_WorkType_ID` | `int DEFAULT NULL` |
| `W_BeginDate` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_EndDate` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Place` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_PlaceState` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Status` | `char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Contact` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_CauseOfLoseDescription` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_Salary` | `double DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7574 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.person_workexp.fk1, Homs.person_workexp.fk2, Homs.person_workexp.fk3, Homs.person_workexp.fk4.

## Homs.personstate → legacy_homs_personstate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.powersource → legacy_homs_powersource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.priest → legacy_homs_priest

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Priest_ID`.
Source indexes: `PRIMARY KEY (`Priest_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.profession_meeting → legacy_homs_profession_meeting

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Career_ID` | `int NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `MeetingDate` | `date NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `InvitedCount` | `smallint NOT NULL DEFAULT '0'` |
| `AttendanceCount` | `smallint NOT NULL DEFAULT '0'` |
| `AbsenceCount` | `smallint NOT NULL DEFAULT '0'` |
| `PaperRegistrar_UserID` | `int DEFAULT NULL` |
| `ProgramRegistrar_UserID` | `int NOT NULL` |
| `Created_At` | `datetime NOT NULL` |
| `Edited_By_UserID` | `int DEFAULT NULL` |
| `Updated_At` | `datetime DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting.fk1, Homs.profession_meeting.fk2, Homs.profession_meeting.fk3, Homs.profession_meeting.fk4, Homs.profession_meeting.fk5.

## Homs.profession_meeting_answer → legacy_homs_profession_meeting_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_answer.fk1, Homs.profession_meeting_answer.fk2.

## Homs.profession_meeting_attendee → legacy_homs_profession_meeting_attendee

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_attendee.fk1, Homs.profession_meeting_attendee.fk2, Homs.profession_meeting_attendee.fk3.

## Homs.profession_meeting_brand → legacy_homs_profession_meeting_brand

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_brand.fk1.

## Homs.profession_meeting_challenge → legacy_homs_profession_meeting_challenge

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_challenge.fk1.

## Homs.profession_meeting_competition → legacy_homs_profession_meeting_competition

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_competition.fk1.

## Homs.profession_meeting_equipment → legacy_homs_profession_meeting_equipment

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_equipment.fk1.

## Homs.profession_meeting_minute → legacy_homs_profession_meeting_minute

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_minute.fk1, Homs.profession_meeting_minute.fk2.

## Homs.profession_meeting_option_answer → legacy_homs_profession_meeting_option_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_option_answer.fk1, Homs.profession_meeting_option_answer.fk2.

## Homs.profession_meeting_participant → legacy_homs_profession_meeting_participant

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Notes` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_participant.fk1, Homs.profession_meeting_participant.fk2, Homs.profession_meeting_participant.fk3, Homs.profession_meeting_participant.fk4.

## Homs.profession_meeting_season → legacy_homs_profession_meeting_season

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_meeting_season.fk1.

## Homs.profession_option → legacy_homs_profession_option

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_option.fk1.

## Homs.profession_question → legacy_homs_profession_question

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Homs.profession_question.fk1.

## Homs.profession_section → legacy_homs_profession_section

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: none declared.

## Homs.purchasing → legacy_homs_purchasing

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `ItemName` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Brand` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ItemCategory_ID` | `int DEFAULT NULL` |
| `Quantity` | `int NOT NULL` |
| `Price` | `decimal(12,2) NOT NULL` |
| `Currency` | `enum('USD','SYP') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `InvoiceDate` | `date DEFAULT NULL` |
| `InvoiceNumber` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `TraderName` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.purchasing.fk1, Homs.purchasing.fk2.

## Homs.purchasingcategory → legacy_homs_purchasingcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.rehabanswer → legacy_homs_rehabanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.rehabanswer.fk1.

## Homs.rehabquestion → legacy_homs_rehabquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.rehabvisit → legacy_homs_rehabvisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans8` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans9` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans10` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans11` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans12` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans13` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans14` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans15` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans8_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans9_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans10_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans11_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans12_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans13_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans14_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans15_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.rehabvisit.fk1.

## Homs.relation → legacy_homs_relation

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.resultstable → legacy_homs_resultstable

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `رقم المشروع` | `int NOT NULL DEFAULT '0'` |
| `حالة الشراكة` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` |
| `نوع التمويل` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `نوع المشروع` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `تفصيل نوع المشروع` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `الصنف` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `المهنة` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `الصورة الشخصية` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `رسالة الرفض` | `int NOT NULL DEFAULT '0'` |
| `زيارة المنزل` | `int NOT NULL DEFAULT '0'` |
| `تاريخ الزيارة` | `date DEFAULT NULL` |
| `توقيت الزيارة` | `varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `MP_VisitNotes` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `ملاحظات عن الزيارة` | `varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` |
| `هل تم تبديل المشروع` | `int NOT NULL DEFAULT '0'` |
| `اسم المشروع` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `المبلغ المطلوب` | `double NOT NULL` |
| `تاريخ تعبئة الطلب` | `date NOT NULL` |
| `مدة تنفيذ المشروع` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `رقم المستفيد` | `int DEFAULT '0'` |
| `اسم المستفيد` | `varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `اسم الأب` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `اسم الأم` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الجنس` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الحالة الاجتماعية` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الحالة` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `الرقم الوطني` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `المواليد` | `int DEFAULT NULL` |
| `خدمة العلم` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `السبب` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الهيئة الوسيطة` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `الطائفة` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `الكاهن المحلي` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `رقم الجوال` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `رقم الأرضي` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الأمانة` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `منطقة المنزل` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `العنوان` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `تسكن مع عائلة أخرى` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `من هم` | `varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `مكان السكن` | `mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `ملاحظة عن مكان السكن` | `varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `أملاك أخرى` | `varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `ملاحظة عن أملاك أخرى` | `mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `عدد أفراد المنزل` | `int DEFAULT NULL` |
| `رقم دفتر العائلة` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `أفراد العائلة` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `العمل الحالي` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `العمل السابق` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `التحصيل العلمي` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `الدورات المتبعة` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `ملاحظات عن الدورات` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `هل أنت المعيل الوحيد` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `المعيلين في المنزل` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `المصادر المساعدة` | `varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `مصادر مساعدة اخرى` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `هل يوجد مصادر طبية` | `int DEFAULT NULL` |
| `المصادر الطبية` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `حالة مرضية` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `خسارات` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `ملاحظة عن الخسارات` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `وصف المشروع` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `استفادة من برامج سابقة` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الجهات السابقة` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `تحليل المشروع` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `هل يحتاج تراخيص` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الترخيص` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `هل يوجد منافسة` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `المنافسون` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `تأمين اللوازم` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الموردون` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `المخاطر` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `ملاحظات عن المخاطر` | `tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `آلية الوقاية` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `معلومات إضافية` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `استراتيجية التسويق` | `mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الربح الصافي` | `mediumint NOT NULL` |
| `ملاحظات عن الربح` | `mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `هل المشروع هو الدخل الوحيد` | `varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` |
| `ملاحظات عن الدخل` | `varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `هل تلتزم بدفتر حسابات` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الميزانية` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci` |
| `مسجل الاستمارة` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `مدخل الاستمارة` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `المعدّل على الاستمارة` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `حالة المشروع` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci` |
| `تاريخ تبديل الحالة` | `date DEFAULT NULL` |
| `الممول` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `المجموعة` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.risk → legacy_homs_risk

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.role → legacy_homs_role

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Role_ID`.
Source indexes: `PRIMARY KEY (`Role_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.score → legacy_homs_score

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.scorevalue → legacy_homs_scorevalue

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.scorevalue.fk1.

## Homs.specialcase → legacy_homs_specialcase

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=295 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.specialcase.fk1, Homs.specialcase.fk2, Homs.specialcase.fk3.

## Homs.specialcasetype → legacy_homs_specialcasetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.state → legacy_homs_state

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.statereason → legacy_homs_statereason

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.street → legacy_homs_street

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.subcategory → legacy_homs_subcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.subcategory.fk1.

## Homs.t1 → legacy_homs_t1

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.task → legacy_homs_task

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `T_ID`.
Source indexes: `PRIMARY KEY (`T_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.task_microproject → legacy_homs_task_microproject

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Source PK components: `TMP_ID`.
Source indexes: `PRIMARY KEY (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=50109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.task_microproject.fk1, Homs.task_microproject.fk2.

## Homs.task_user → legacy_homs_task_user

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `TU_ID`.
Source indexes: `PRIMARY KEY (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=298 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.task_user.fk1, Homs.task_user.fk2.

## Homs.user → legacy_homs_user

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `UserID` | `int NOT NULL` |
| `UserName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Password` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Email` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT ''` |
| `UserRoleID` | `int NOT NULL` |
| `IsVisitor` | `int NOT NULL` |
| `IsME` | `int NOT NULL` |
| `IsCommunication` | `int NOT NULL` |
| `IsCashier` | `int NOT NULL DEFAULT '0'` |
| `ProfilePicture` | `mediumblob` |

Source PK components: `UserID`.
Source indexes: `PRIMARY KEY (`UserID`)`; `KEY `FK_User_Role` (`UserRoleID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.user.fk1.

## Homs.user_notification → legacy_homs_user_notification

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Body` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Seen` | `int NOT NULL` |
| `P_Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Sender_ID` | `int DEFAULT NULL` |
| `Pay_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=167567 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.user_notification.fk1, Homs.user_notification.fk2, Homs.user_notification.fk3.

## Homs.version → legacy_homs_version

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.visit → legacy_homs_visit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Kind` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `VehicleNumber` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1436 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.visit.fk1.

## Homs.visit_schedule_temp → legacy_homs_visit_schedule_temp

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MicroProject_ID` | `int DEFAULT NULL` |
| `beneficiary` | `varchar(28) DEFAULT NULL` |
| `visit_type` | `varchar(14) DEFAULT NULL` |
| `date` | `varchar(10) DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3;`.
Relationship mappings: none declared.

## Homs.visit_user → legacy_homs_visit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1912 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.visit_user.fk1, Homs.visit_user.fk2.

## Homs.visitanswers → legacy_homs_visitanswers

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Homs.visitanswers.fk1.

## Homs.visitquestions → legacy_homs_visitquestions

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.work → legacy_homs_work

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `W_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Source PK components: `W_ID`.
Source indexes: `PRIMARY KEY (`W_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Homs.worktype → legacy_homs_worktype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.aganswer → legacy_latakia_aganswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.aganswer.fk1.

## Latakia.age → legacy_latakia_age

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.agquestion → legacy_latakia_agquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.aid → legacy_latakia_aid

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.aidperiod → legacy_latakia_aidperiod

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.aidsource → legacy_latakia_aidsource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.asset → legacy_latakia_asset

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.assetbenefits → legacy_latakia_assetbenefits

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.assettype → legacy_latakia_assettype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.backgrounds → legacy_latakia_backgrounds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.career → legacy_latakia_career

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.career.fk1.

## Latakia.category → legacy_latakia_category

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.checklist → legacy_latakia_checklist

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int NOT NULL` |
| `HasHomeVisit` | `int NOT NULL DEFAULT '-1'` |
| `HVisit_Date` | `date DEFAULT NULL` |
| `HVisit_Time` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `HVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `HVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `HVisit_Note` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `HasProjectVisit` | `int NOT NULL DEFAULT '-1'` |
| `PVisit_Date` | `date DEFAULT NULL` |
| `PVisit_Time` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `PVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `PVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `PVisit_Note` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `VisitsReport` | `text COLLATE utf8mb4_bin NOT NULL` |
| `NeedProTest` | `int NOT NULL DEFAULT '-1'` |
| `NeedProTest_Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `HasProTest` | `int NOT NULL DEFAULT '-1'` |
| `ProTest_Date` | `date DEFAULT NULL` |
| `ProTest_Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `ProTest_User_ID` | `int DEFAULT NULL` |
| `ProTest_Result` | `text COLLATE utf8mb4_bin NOT NULL` |
| `HasProTrain` | `int NOT NULL DEFAULT '-1'` |
| `ProTrain_Trainer` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Place` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_BDate` | `date DEFAULT NULL` |
| `ProTrain_EDate` | `date DEFAULT NULL` |
| `ProTrain_Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Result` | `text COLLATE utf8mb4_bin NOT NULL` |
| `HasMngTrain` | `int NOT NULL DEFAULT '-1'` |
| `MngTrain_Trainer` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Place` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Date` | `date DEFAULT NULL` |
| `MngTrain_Result` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `TeamDate` | `date DEFAULT NULL` |
| `CommityDate` | `date DEFAULT NULL` |
| `RejectMsg` | `int NOT NULL DEFAULT '-1'` |
| `RejectMsgDate` | `date DEFAULT NULL` |
| `RejectMsgSender_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=761 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.checklist.fk1, Latakia.checklist.fk2, Latakia.checklist.fk3, Latakia.checklist.fk4, Latakia.checklist.fk5, Latakia.checklist.fk6, Latakia.checklist.fk7.

## Latakia.checklist_member → legacy_latakia_checklist_member

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.checklist_member.fk1.

## Latakia.checklist_opinion → legacy_latakia_checklist_opinion

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.checklist_position → legacy_latakia_checklist_position

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.course → legacy_latakia_course

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.databases → legacy_latakia_databases

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.donor → legacy_latakia_donor

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.donorgroup → legacy_latakia_donorgroup

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.donorgroup.fk1.

## Latakia.education → legacy_latakia_education

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.educationlevel → legacy_latakia_educationlevel

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.educationtype → legacy_latakia_educationtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.exefile → legacy_latakia_exefile

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ExeF_ID`.
Source indexes: `PRIMARY KEY (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.exefile.fk1.

## Latakia.family → legacy_latakia_family

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `F_ID`.
Source indexes: `PRIMARY KEY (`F_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=604 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: none declared.

## Latakia.file → legacy_latakia_file

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `File_ID`.
Source indexes: `PRIMARY KEY (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.file.fk1.

## Latakia.fundtype → legacy_latakia_fundtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.goal → legacy_latakia_goal

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.illness → legacy_latakia_illness

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.image → legacy_latakia_image

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `Image_ID`.
Source indexes: `PRIMARY KEY (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6270 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.image.fk1, Latakia.image.fk2, Latakia.image.fk3.

## Latakia.imagetype → legacy_latakia_imagetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.incomesource → legacy_latakia_incomesource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.language → legacy_latakia_language

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `L_ID`.
Source indexes: `PRIMARY KEY (`L_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.level → legacy_latakia_level

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Level_ID`.
Source indexes: `PRIMARY KEY (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.loan → legacy_latakia_loan

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Loan_ID` | `int NOT NULL` |
| `Currency` | `enum('USD','SP') COLLATE utf8mb4_bin NOT NULL` |
| `ExchangeRate` | `decimal(12,2) NOT NULL DEFAULT '1.00'` |
| `Loan_Amount` | `decimal(12,2) NOT NULL DEFAULT '0.00'` |
| `Loan_DateTaken` | `date DEFAULT NULL` |
| `Loan_PaymentsCount` | `float DEFAULT NULL` |
| `Loan_Rate` | `float DEFAULT NULL` |
| `Loan_ReturnedAmount` | `float NOT NULL DEFAULT '0'` |
| `Loan_PaymentsAmount` | `float DEFAULT NULL` |
| `Loan_Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ReceiptID` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `Loan_ID`.
Source indexes: `PRIMARY KEY (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;`.
Relationship mappings: Latakia.loan.fk1.

## Latakia.log → legacy_latakia_log

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Source PK components: `Log_ID`.
Source indexes: `PRIMARY KEY (`Log_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=76775 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.loss → legacy_latakia_loss

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.manualfile → legacy_latakia_manualfile

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.maritalstatus → legacy_latakia_maritalstatus

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.material → legacy_latakia_material

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4343 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.material.fk1.

## Latakia.meanswer → legacy_latakia_meanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.meanswer.fk1.

## Latakia.medicalneeds → legacy_latakia_medicalneeds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.meprofitmarks → legacy_latakia_meprofitmarks

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.mequestion → legacy_latakia_mequestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.meresultranges → legacy_latakia_meresultranges

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.mevisit → legacy_latakia_mevisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Number` | `varchar(2) COLLATE utf8mb4_bin NOT NULL` |
| `Kind` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Profit` | `double NOT NULL` |
| `Ans1` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Result` | `int NOT NULL` |
| `Created_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.mevisit.fk1.

## Latakia.mevisit_answer → legacy_latakia_mevisit_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1075 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.mevisit_answer.fk1, Latakia.mevisit_answer.fk2, Latakia.mevisit_answer.fk3.

## Latakia.mevisit_user → legacy_latakia_mevisit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.mevisit_user.fk1, Latakia.mevisit_user.fk2.

## Latakia.microproject → legacy_latakia_microproject

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MP_ID` | `int NOT NULL` |
| `MP_DateOfRequest` | `date DEFAULT NULL` |
| `MP_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `MP_RequestedAmount` | `double NOT NULL` |
| `MP_FundType_ID` | `int DEFAULT '1'` |
| `MP_Type_ID` | `int DEFAULT NULL` |
| `MP_SubType_ID` | `int DEFAULT NULL` |
| `Category_ID` | `int DEFAULT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `Career_ID` | `int DEFAULT NULL` |
| `MP_Speciality` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `MP_Profit_Description` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind_Note` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceType_ID` | `int DEFAULT NULL` |
| `MP_PlaceAddress` | `text COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceNote` | `text COLLATE utf8mb4_bin NOT NULL` |
| `MP_AmperNumber` | `int NOT NULL DEFAULT '0'` |
| `MP_AmperType` | `int NOT NULL DEFAULT '0'` |
| `MP_Agreement1` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement2` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement3` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement4` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement5` | `int NOT NULL DEFAULT '-1'` |
| `MP_Note` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `MP_Donor` | `int DEFAULT NULL` |
| `DonorGroup_ID` | `int DEFAULT NULL` |
| `Partnership` | `int NOT NULL DEFAULT '0'` |
| `IsContentUpdated` | `int NOT NULL DEFAULT '0'` |
| `MP_Street_ID` | `int DEFAULT NULL` |
| `MP_AddressAfterFund` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `MP_PaperUser_ID` | `int DEFAULT NULL` |
| `MP_ProgramUser_ID` | `int DEFAULT NULL` |
| `MP_EditedByUser_ID` | `int DEFAULT NULL` |
| `ApplicationState` | `varchar(16) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `MP_ID`.
Source indexes: `PRIMARY KEY (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_MP_SubCategory` (`SubCategory_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=594 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microproject.fk1, Latakia.microproject.fk2, Latakia.microproject.fk3, Latakia.microproject.fk4, Latakia.microproject.fk5, Latakia.microproject.fk6, Latakia.microproject.fk7, Latakia.microproject.fk8, Latakia.microproject.fk9, Latakia.microproject.fk10, Latakia.microproject.fk11, Latakia.microproject.fk12, Latakia.microproject.fk13.

## Latakia.microproject_ag_answer → legacy_latakia_microproject_ag_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Source PK components: `MicroProject_ID`, `Question_ID`, `Answer_ID`.
Source indexes: `PRIMARY KEY (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microproject_ag_answer.fk1.

## Latakia.microproject_powersource → legacy_latakia_microproject_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=350 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microproject_powersource.fk1, Latakia.microproject_powersource.fk2.

## Latakia.microproject_rehab → legacy_latakia_microproject_rehab

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microproject_rehab.fk1.

## Latakia.microproject_rehab_answer → legacy_latakia_microproject_rehab_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microproject_rehab_answer.fk1, Latakia.microproject_rehab_answer.fk2, Latakia.microproject_rehab_answer.fk3.

## Latakia.microproject_risk → legacy_latakia_microproject_risk

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microproject_risk.fk1, Latakia.microproject_risk.fk2.

## Latakia.microproject_score → legacy_latakia_microproject_score

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8702 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microproject_score.fk1, Latakia.microproject_score.fk2.

## Latakia.microproject_state → legacy_latakia_microproject_state

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2144 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microproject_state.fk1, Latakia.microproject_state.fk2, Latakia.microproject_state.fk3.

## Latakia.microprojectsubtype → legacy_latakia_microprojectsubtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.microprojectsubtype.fk1.

## Latakia.microprojecttype → legacy_latakia_microprojecttype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.militarystate → legacy_latakia_militarystate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.office_monitoring → legacy_latakia_office_monitoring

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text COLLATE utf8mb4_bin NOT NULL` |
| `OtherComments` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.office_monitoring.fk1.

## Latakia.parish → legacy_latakia_parish

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.payment → legacy_latakia_payment

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Pay_ID`.
Source indexes: `PRIMARY KEY (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1660 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: Latakia.payment.fk1.

## Latakia.person → legacy_latakia_person

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `P_ID` | `int NOT NULL` |
| `P_FirstName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `P_LastName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `P_FatherName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `P_MotherName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `P_FullName_en` | `mediumtext COLLATE utf8mb4_bin` |
| `P_DOB` | `date DEFAULT NULL` |
| `P_MilitaryService` | `varchar(200) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryService_Date` | `date DEFAULT NULL` |
| `P_MilitaryService_Note` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryPeriod` | `int DEFAULT NULL` |
| `P_IsMilitaryInWar` | `int DEFAULT '-1'` |
| `P_Mobile` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `P_Mobile2` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_HomeTel` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Street_ID` | `int DEFAULT NULL` |
| `P_HomeAddress` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `P_NationalNumber` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_RegistrationPlace` | `varchar(200) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Sex` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `P_MaritalStatus_ID` | `int DEFAULT NULL` |
| `P_NumAtHome` | `int NOT NULL` |
| `P_NumOfMembers` | `int NOT NULL` |
| `IsProjectOwner` | `varchar(10) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_IntermidiarySide` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `P_HomeType_ID` | `int DEFAULT NULL` |
| `P_HomeState_Note` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `P_MaristesCourse` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `P_Parish_ID` | `int DEFAULT NULL` |
| `P_Priest_ID` | `int DEFAULT NULL` |
| `P_Priest2_ID` | `int DEFAULT NULL` |
| `P_State_ID` | `int DEFAULT '1'` |
| `P_Goal` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `P_GoalHow` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `P_OtherNotes` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `P_WorkYears` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `P_ID`.
Source indexes: `PRIMARY KEY (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2576 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person.fk1, Latakia.person.fk2, Latakia.person.fk3, Latakia.person.fk4, Latakia.person.fk5, Latakia.person.fk6, Latakia.person.fk7.

## Latakia.person_aidsource → legacy_latakia_person_aidsource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_aidsource.fk1.

## Latakia.person_asset → legacy_latakia_person_asset

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_asset.fk1, Latakia.person_asset.fk2, Latakia.person_asset.fk3, Latakia.person_asset.fk4.

## Latakia.person_checklistmembers → legacy_latakia_person_checklistmembers

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Member_ID` | `int DEFAULT NULL` |
| `Opinion` | `int NOT NULL` |
| `Notes` | `text COLLATE utf8mb4_bin NOT NULL` |
| `NameNotes` | `varchar(400) COLLATE utf8mb4_bin NOT NULL` |
| `RoleNotes` | `varchar(500) COLLATE utf8mb4_bin NOT NULL` |
| `Location` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4632 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_checklistmembers.fk1, Latakia.person_checklistmembers.fk2.

## Latakia.person_course → legacy_latakia_person_course

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=435 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_course.fk1, Latakia.person_course.fk2.

## Latakia.person_education → legacy_latakia_person_education

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=423 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_education.fk1, Latakia.person_education.fk2, Latakia.person_education.fk3.

## Latakia.person_family → legacy_latakia_person_family

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Family_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Relation_ID` | `int DEFAULT NULL` |
| `EducationLevel_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `HasWork` | `int DEFAULT '-1'` |
| `P_Provider_ID` | `int DEFAULT NULL` |
| `Work_Name` | `longtext COLLATE utf8mb4_bin` |
| `PF_ID` | `int NOT NULL` |

Source PK components: `PF_ID`.
Source indexes: `PRIMARY KEY (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2563 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_family.fk1, Latakia.person_family.fk2, Latakia.person_family.fk3, Latakia.person_family.fk4, Latakia.person_family.fk5, Latakia.person_family.fk6.

## Latakia.person_goals → legacy_latakia_person_goals

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=927 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_goals.fk1, Latakia.person_goals.fk2.

## Latakia.person_help → legacy_latakia_person_help

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_help.fk1, Latakia.person_help.fk2, Latakia.person_help.fk3, Latakia.person_help.fk4.

## Latakia.person_illness → legacy_latakia_person_illness

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Illness_ID` | `int DEFAULT NULL` |
| `Has_MedicalAid` | `int NOT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Donor` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `AidPeriod_ID` | `int DEFAULT '0'` |
| `MedicalNeeds_ID` | `int DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_illness.fk1, Latakia.person_illness.fk2, Latakia.person_illness.fk3, Latakia.person_illness.fk4, Latakia.person_illness.fk5.

## Latakia.person_loss → legacy_latakia_person_loss

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=383 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_loss.fk1, Latakia.person_loss.fk2.

## Latakia.person_microproject → legacy_latakia_person_microproject

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `PMP_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PersonType` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Loss` | `longtext COLLATE utf8mb4_bin` |
| `P_Goal` | `longtext COLLATE utf8mb4_bin` |
| `P_GoalHow` | `longtext COLLATE utf8mb4_bin` |
| `P_OtherNotes` | `longtext COLLATE utf8mb4_bin` |
| `P_HasWork` | `int NOT NULL DEFAULT '-1'` |
| `P_WorkYears` | `mediumtext COLLATE utf8mb4_bin` |
| `P_IsLivingWithFamily` | `int NOT NULL DEFAULT '-1'` |
| `P_LiveWithFamily_Note` | `mediumtext COLLATE utf8mb4_bin` |
| `P_HasEducation` | `int NOT NULL DEFAULT '-1'` |
| `P_HasCourses` | `int NOT NULL DEFAULT '-1'` |
| `P_HasLoss` | `int NOT NULL DEFAULT '-1'` |
| `P_HasIllness` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAssets` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAidSource` | `int NOT NULL DEFAULT '-1'` |
| `P_HasHelp` | `int NOT NULL DEFAULT '-1'` |
| `P_HasDebts` | `int NOT NULL DEFAULT '-1'` |
| `P_DebtsAmount` | `text COLLATE utf8mb4_bin` |
| `P_DebtsNote` | `text COLLATE utf8mb4_bin` |
| `P_AidSourceNote` | `text COLLATE utf8mb4_bin` |

Source PK components: `PMP_ID`.
Source indexes: `PRIMARY KEY (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=612 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_microproject.fk1, Latakia.person_microproject.fk2.

## Latakia.person_powersource → legacy_latakia_person_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_powersource.fk1, Latakia.person_powersource.fk2.

## Latakia.person_workexp → legacy_latakia_person_workexp

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Work_ID` | `int DEFAULT NULL` |
| `W_Description` | `text COLLATE utf8mb4_bin` |
| `W_WorkType_ID` | `int DEFAULT NULL` |
| `W_BeginDate` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `W_EndDate` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `W_Place` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `W_PlaceState` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `W_Status` | `char(50) COLLATE utf8mb4_bin NOT NULL` |
| `W_Contact` | `longtext COLLATE utf8mb4_bin` |
| `W_CauseOfLoseDescription` | `longtext COLLATE utf8mb4_bin` |
| `W_Salary` | `double DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1313 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.person_workexp.fk1, Latakia.person_workexp.fk2, Latakia.person_workexp.fk3, Latakia.person_workexp.fk4.

## Latakia.personstate → legacy_latakia_personstate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.powersource → legacy_latakia_powersource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.priest → legacy_latakia_priest

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Priest_ID`.
Source indexes: `PRIMARY KEY (`Priest_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.profession_meeting → legacy_latakia_profession_meeting

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Career_ID` | `int NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `MeetingDate` | `date NOT NULL` |
| `Place` | `varchar(200) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `InvitedCount` | `smallint NOT NULL DEFAULT '0'` |
| `AttendanceCount` | `smallint NOT NULL DEFAULT '0'` |
| `AbsenceCount` | `smallint NOT NULL DEFAULT '0'` |
| `PaperRegistrar_UserID` | `int DEFAULT NULL` |
| `ProgramRegistrar_UserID` | `int NOT NULL` |
| `Created_At` | `datetime NOT NULL` |
| `Edited_By_UserID` | `int DEFAULT NULL` |
| `Updated_At` | `datetime DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting.fk1, Latakia.profession_meeting.fk2, Latakia.profession_meeting.fk3, Latakia.profession_meeting.fk4, Latakia.profession_meeting.fk5.

## Latakia.profession_meeting_answer → legacy_latakia_profession_meeting_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_answer.fk1, Latakia.profession_meeting_answer.fk2.

## Latakia.profession_meeting_attendee → legacy_latakia_profession_meeting_attendee

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_attendee.fk1, Latakia.profession_meeting_attendee.fk2, Latakia.profession_meeting_attendee.fk3.

## Latakia.profession_meeting_brand → legacy_latakia_profession_meeting_brand

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_brand.fk1.

## Latakia.profession_meeting_challenge → legacy_latakia_profession_meeting_challenge

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_challenge.fk1.

## Latakia.profession_meeting_competition → legacy_latakia_profession_meeting_competition

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_competition.fk1.

## Latakia.profession_meeting_equipment → legacy_latakia_profession_meeting_equipment

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_equipment.fk1.

## Latakia.profession_meeting_minute → legacy_latakia_profession_meeting_minute

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text COLLATE utf8mb3_unicode_ci` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_minute.fk1, Latakia.profession_meeting_minute.fk2.

## Latakia.profession_meeting_option_answer → legacy_latakia_profession_meeting_option_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_option_answer.fk1, Latakia.profession_meeting_option_answer.fk2.

## Latakia.profession_meeting_participant → legacy_latakia_profession_meeting_participant

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_Name` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Notes` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_participant.fk1, Latakia.profession_meeting_participant.fk2, Latakia.profession_meeting_participant.fk3, Latakia.profession_meeting_participant.fk4.

## Latakia.profession_meeting_season → legacy_latakia_profession_meeting_season

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_meeting_season.fk1.

## Latakia.profession_option → legacy_latakia_profession_option

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_option.fk1.

## Latakia.profession_question → legacy_latakia_profession_question

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: Latakia.profession_question.fk1.

## Latakia.profession_section → legacy_latakia_profession_section

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: none declared.

## Latakia.purchasing → legacy_latakia_purchasing

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `ItemName` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `Brand` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |
| `ItemCategory_ID` | `int DEFAULT NULL` |
| `Quantity` | `int NOT NULL` |
| `Price` | `decimal(12,2) NOT NULL` |
| `Currency` | `enum('USD','SYP') COLLATE utf8mb4_bin NOT NULL` |
| `InvoiceDate` | `date DEFAULT NULL` |
| `InvoiceNumber` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `TraderName` | `varchar(300) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.purchasing.fk1, Latakia.purchasing.fk2.

## Latakia.purchasingcategory → legacy_latakia_purchasingcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.rehabanswer → legacy_latakia_rehabanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.rehabanswer.fk1.

## Latakia.rehabquestion → legacy_latakia_rehabquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.rehabvisit → legacy_latakia_rehabvisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans8` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans9` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans10` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans11` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans12` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans13` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans14` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans15` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans8_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans9_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans10_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans11_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans12_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans13_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans14_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans15_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.rehabvisit.fk1.

## Latakia.relation → legacy_latakia_relation

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.risk → legacy_latakia_risk

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.role → legacy_latakia_role

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Role_ID`.
Source indexes: `PRIMARY KEY (`Role_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.score → legacy_latakia_score

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.scorevalue → legacy_latakia_scorevalue

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.scorevalue.fk1.

## Latakia.specialcase → legacy_latakia_specialcase

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.specialcase.fk1, Latakia.specialcase.fk2, Latakia.specialcase.fk3.

## Latakia.specialcasetype → legacy_latakia_specialcasetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.state → legacy_latakia_state

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.statereason → legacy_latakia_statereason

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.street → legacy_latakia_street

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.subcategory → legacy_latakia_subcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.subcategory.fk1.

## Latakia.t1 → legacy_latakia_t1

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.task → legacy_latakia_task

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `T_ID`.
Source indexes: `PRIMARY KEY (`T_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.task_microproject → legacy_latakia_task_microproject

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Source PK components: `TMP_ID`.
Source indexes: `PRIMARY KEY (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14337 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.task_microproject.fk1, Latakia.task_microproject.fk2.

## Latakia.task_user → legacy_latakia_task_user

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `TU_ID`.
Source indexes: `PRIMARY KEY (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.task_user.fk1, Latakia.task_user.fk2.

## Latakia.user → legacy_latakia_user

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `UserID` | `int NOT NULL` |
| `UserName` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Password` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Email` | `varchar(150) COLLATE utf8mb4_bin NOT NULL DEFAULT ''` |
| `UserRoleID` | `int NOT NULL` |
| `IsVisitor` | `int NOT NULL` |
| `IsME` | `int NOT NULL` |
| `IsCommunication` | `int NOT NULL` |
| `IsCashier` | `int NOT NULL DEFAULT '0'` |
| `ProfilePicture` | `mediumblob` |

Source PK components: `UserID`.
Source indexes: `PRIMARY KEY (`UserID`)`; `UNIQUE KEY `UserName` (`UserName`)`; `KEY `FK_User_Role` (`UserRoleID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.user.fk1.

## Latakia.user_notification → legacy_latakia_user_notification

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Body` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Seen` | `int NOT NULL` |
| `P_Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Sender_ID` | `int DEFAULT NULL` |
| `Pay_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=26318 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.user_notification.fk1, Latakia.user_notification.fk2, Latakia.user_notification.fk3.

## Latakia.version → legacy_latakia_version

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.visit → legacy_latakia_visit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Kind` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Ans1` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `VehicleNumber` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.visit.fk1.

## Latakia.visit_user → legacy_latakia_visit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.visit_user.fk1, Latakia.visit_user.fk2.

## Latakia.visitanswers → legacy_latakia_visitanswers

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: Latakia.visitanswers.fk1.

## Latakia.visitquestions → legacy_latakia_visitquestions

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.work → legacy_latakia_work

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `W_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Source PK components: `W_ID`.
Source indexes: `PRIMARY KEY (`W_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## Latakia.worktype → legacy_latakia_worktype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.aganswer → legacy_alwadi_aganswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.aganswer.fk1.

## AlWadi.age → legacy_alwadi_age

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.agquestion → legacy_alwadi_agquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.aid → legacy_alwadi_aid

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.aidperiod → legacy_alwadi_aidperiod

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.aidsource → legacy_alwadi_aidsource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.asset → legacy_alwadi_asset

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.assetbenefits → legacy_alwadi_assetbenefits

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.assettype → legacy_alwadi_assettype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.backgrounds → legacy_alwadi_backgrounds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.career → legacy_alwadi_career

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.career.fk1.

## AlWadi.category → legacy_alwadi_category

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.checklist → legacy_alwadi_checklist

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int NOT NULL` |
| `HasHomeVisit` | `int NOT NULL DEFAULT '-1'` |
| `HVisit_Date` | `date DEFAULT NULL` |
| `HVisit_Time` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `HVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `HVisit_Note` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProjectVisit` | `int NOT NULL DEFAULT '-1'` |
| `PVisit_Date` | `date DEFAULT NULL` |
| `PVisit_Time` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PVisit_Visitor1_ID` | `int DEFAULT NULL` |
| `PVisit_Visitor2_ID` | `int DEFAULT NULL` |
| `PVisit_Note` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `VisitsReport` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `NeedProTest` | `int NOT NULL DEFAULT '-1'` |
| `NeedProTest_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProTest` | `int NOT NULL DEFAULT '-1'` |
| `ProTest_Date` | `date DEFAULT NULL` |
| `ProTest_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTest_User_ID` | `int DEFAULT NULL` |
| `ProTest_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasProTrain` | `int NOT NULL DEFAULT '-1'` |
| `ProTrain_Trainer` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Place` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_BDate` | `date DEFAULT NULL` |
| `ProTrain_EDate` | `date DEFAULT NULL` |
| `ProTrain_Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProTrain_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasMngTrain` | `int NOT NULL DEFAULT '-1'` |
| `MngTrain_Trainer` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Place` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MngTrain_Date` | `date DEFAULT NULL` |
| `MngTrain_Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `TeamDate` | `date DEFAULT NULL` |
| `CommityDate` | `date DEFAULT NULL` |
| `RejectMsg` | `int NOT NULL DEFAULT '-1'` |
| `RejectMsgDate` | `date DEFAULT NULL` |
| `RejectMsgSender_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=558 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.checklist.fk1, AlWadi.checklist.fk2, AlWadi.checklist.fk3, AlWadi.checklist.fk4, AlWadi.checklist.fk5, AlWadi.checklist.fk6, AlWadi.checklist.fk7.

## AlWadi.checklist_member → legacy_alwadi_checklist_member

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.checklist_member.fk1.

## AlWadi.checklist_opinion → legacy_alwadi_checklist_opinion

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.checklist_position → legacy_alwadi_checklist_position

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.course → legacy_alwadi_course

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.databases → legacy_alwadi_databases

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.donor → legacy_alwadi_donor

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.donorgroup → legacy_alwadi_donorgroup

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.donorgroup.fk1.

## AlWadi.education → legacy_alwadi_education

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.educationlevel → legacy_alwadi_educationlevel

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.educationtype → legacy_alwadi_educationtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.exefile → legacy_alwadi_exefile

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ExeF_ID`.
Source indexes: `PRIMARY KEY (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.exefile.fk1.

## AlWadi.family → legacy_alwadi_family

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `F_ID`.
Source indexes: `PRIMARY KEY (`F_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2731 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: none declared.

## AlWadi.file → legacy_alwadi_file

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `File_ID`.
Source indexes: `PRIMARY KEY (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.file.fk1.

## AlWadi.fundtype → legacy_alwadi_fundtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.goal → legacy_alwadi_goal

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.illness → legacy_alwadi_illness

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.image → legacy_alwadi_image

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `Image_ID`.
Source indexes: `PRIMARY KEY (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14970 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.image.fk1, AlWadi.image.fk2, AlWadi.image.fk3.

## AlWadi.imagetype → legacy_alwadi_imagetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.incomesource → legacy_alwadi_incomesource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.language → legacy_alwadi_language

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `L_ID`.
Source indexes: `PRIMARY KEY (`L_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.level → legacy_alwadi_level

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Level_ID`.
Source indexes: `PRIMARY KEY (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.loan → legacy_alwadi_loan

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Loan_ID` | `int NOT NULL` |
| `Currency` | `enum('USD','SP') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExchangeRate` | `decimal(12,2) NOT NULL DEFAULT '1.00'` |
| `Loan_Amount` | `decimal(12,2) NOT NULL DEFAULT '0.00'` |
| `Loan_DateTaken` | `date DEFAULT NULL` |
| `Loan_PaymentsCount` | `float DEFAULT NULL` |
| `Loan_Rate` | `float DEFAULT NULL` |
| `Loan_ReturnedAmount` | `double NOT NULL DEFAULT '0'` |
| `Loan_PaymentsAmount` | `double NOT NULL DEFAULT '0'` |
| `Loan_Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ReceiptID` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `Loan_ID`.
Source indexes: `PRIMARY KEY (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1094 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=COMPACT;`.
Relationship mappings: AlWadi.loan.fk1.

## AlWadi.log → legacy_alwadi_log

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Source PK components: `Log_ID`.
Source indexes: `PRIMARY KEY (`Log_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=57412 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.loss → legacy_alwadi_loss

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.manualfile → legacy_alwadi_manualfile

Attachment metadata/BLOB

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.maritalstatus → legacy_alwadi_maritalstatus

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.material → legacy_alwadi_material

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1221 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.material.fk1.

## AlWadi.meanswer → legacy_alwadi_meanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.meanswer.fk1.

## AlWadi.medicalneeds → legacy_alwadi_medicalneeds

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.meprofitmarks → legacy_alwadi_meprofitmarks

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.mequestion → legacy_alwadi_mequestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.meresultranges → legacy_alwadi_meresultranges

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.mevisit → legacy_alwadi_mevisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Number` | `varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Kind` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Profit` | `double NOT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Result` | `int NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2770 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.mevisit.fk1.

## AlWadi.mevisit_answer → legacy_alwadi_mevisit_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=82477 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.mevisit_answer.fk1, AlWadi.mevisit_answer.fk2, AlWadi.mevisit_answer.fk3.

## AlWadi.mevisit_user → legacy_alwadi_mevisit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3817 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.mevisit_user.fk1, AlWadi.mevisit_user.fk2.

## AlWadi.microproject → legacy_alwadi_microproject

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MP_ID` | `int NOT NULL` |
| `MP_DateOfRequest` | `date DEFAULT NULL` |
| `MP_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_RequestedAmount` | `double NOT NULL` |
| `MP_FundType_ID` | `int DEFAULT '1'` |
| `MP_Type_ID` | `int DEFAULT NULL` |
| `MP_SubType_ID` | `int DEFAULT NULL` |
| `Category_ID` | `int DEFAULT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `Career_ID` | `int DEFAULT NULL` |
| `MP_Speciality` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_Profit_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_IncomeKind_Note` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceType_ID` | `int DEFAULT NULL` |
| `MP_PlaceAddress` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PlaceNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_AmperNumber` | `int NOT NULL DEFAULT '0'` |
| `MP_AmperType` | `int NOT NULL DEFAULT '0'` |
| `MP_Agreement1` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement2` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement3` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement4` | `int NOT NULL DEFAULT '-1'` |
| `MP_Agreement5` | `int NOT NULL DEFAULT '-1'` |
| `MP_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_Donor` | `int DEFAULT NULL` |
| `DonorGroup_ID` | `int DEFAULT NULL` |
| `Partnership` | `int NOT NULL DEFAULT '0'` |
| `IsContentUpdated` | `int NOT NULL DEFAULT '0'` |
| `MP_Street_ID` | `int DEFAULT NULL` |
| `MP_AddressAfterFund` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MP_PaperUser_ID` | `int DEFAULT NULL` |
| `MP_ProgramUser_ID` | `int DEFAULT NULL` |
| `MP_EditedByUser_ID` | `int DEFAULT NULL` |
| `ApplicationState` | `varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `MP_ID`.
Source indexes: `PRIMARY KEY (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_SubCategory_MicroProject` (`SubCategory_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2663 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microproject.fk1, AlWadi.microproject.fk2, AlWadi.microproject.fk3, AlWadi.microproject.fk4, AlWadi.microproject.fk5, AlWadi.microproject.fk6, AlWadi.microproject.fk7, AlWadi.microproject.fk8, AlWadi.microproject.fk9, AlWadi.microproject.fk10, AlWadi.microproject.fk11, AlWadi.microproject.fk12, AlWadi.microproject.fk13.

## AlWadi.microproject_ag_answer → legacy_alwadi_microproject_ag_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Source PK components: `MicroProject_ID`, `Question_ID`, `Answer_ID`.
Source indexes: `PRIMARY KEY (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microproject_ag_answer.fk1.

## AlWadi.microproject_powersource → legacy_alwadi_microproject_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microproject_powersource.fk1, AlWadi.microproject_powersource.fk2.

## AlWadi.microproject_rehab → legacy_alwadi_microproject_rehab

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microproject_rehab.fk1.

## AlWadi.microproject_rehab_answer → legacy_alwadi_microproject_rehab_answer

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microproject_rehab_answer.fk1, AlWadi.microproject_rehab_answer.fk2, AlWadi.microproject_rehab_answer.fk3.

## AlWadi.microproject_risk → legacy_alwadi_microproject_risk

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1008 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microproject_risk.fk1, AlWadi.microproject_risk.fk2.

## AlWadi.microproject_score → legacy_alwadi_microproject_score

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=96677 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microproject_score.fk1, AlWadi.microproject_score.fk2.

## AlWadi.microproject_state → legacy_alwadi_microproject_state

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8640 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microproject_state.fk1, AlWadi.microproject_state.fk2, AlWadi.microproject_state.fk3.

## AlWadi.microprojectsubtype → legacy_alwadi_microprojectsubtype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.microprojectsubtype.fk1.

## AlWadi.microprojecttype → legacy_alwadi_microprojecttype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.militarystate → legacy_alwadi_militarystate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.office_monitoring → legacy_alwadi_office_monitoring

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `OtherComments` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.office_monitoring.fk1.

## AlWadi.parish → legacy_alwadi_parish

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.payment → legacy_alwadi_payment

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL DEFAULT '0'` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Pay_ID`.
Source indexes: `PRIMARY KEY (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=14268 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;`.
Relationship mappings: AlWadi.payment.fk1.

## AlWadi.person → legacy_alwadi_person

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `P_ID` | `int NOT NULL` |
| `P_FirstName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_LastName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_FatherName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MotherName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_FullName_en` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_DOB` | `date DEFAULT NULL` |
| `P_MilitaryService` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryService_Date` | `date DEFAULT NULL` |
| `P_MilitaryService_Note` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_MilitaryPeriod` | `int DEFAULT NULL` |
| `P_IsMilitaryInWar` | `int DEFAULT '-1'` |
| `P_Mobile` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_Mobile2` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_HomeTel` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Street_ID` | `int DEFAULT NULL` |
| `P_HomeAddress` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_NationalNumber` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_RegistrationPlace` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Sex` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MaritalStatus_ID` | `int DEFAULT NULL` |
| `P_NumAtHome` | `int NOT NULL` |
| `P_NumOfMembers` | `int NOT NULL` |
| `IsProjectOwner` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_IntermidiarySide` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_HomeType_ID` | `int DEFAULT NULL` |
| `P_HomeState_Note` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_MaristesCourse` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `P_Parish_ID` | `int DEFAULT NULL` |
| `P_Priest_ID` | `int DEFAULT NULL` |
| `P_Priest2_ID` | `int DEFAULT NULL` |
| `P_State_ID` | `int DEFAULT '1'` |

Source PK components: `P_ID`.
Source indexes: `PRIMARY KEY (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12301 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person.fk1, AlWadi.person.fk2, AlWadi.person.fk3, AlWadi.person.fk4, AlWadi.person.fk5, AlWadi.person.fk6, AlWadi.person.fk7.

## AlWadi.person_aidsource → legacy_alwadi_person_aidsource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `Person_AidSource1` (`AidSource_ID`)`; `KEY `Person_AidSource2` (`Person_ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_aidsource.fk1, AlWadi.person_aidsource.fk2, AlWadi.person_aidsource.fk3.

## AlWadi.person_asset → legacy_alwadi_person_asset

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=761 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_asset.fk1, AlWadi.person_asset.fk2, AlWadi.person_asset.fk3, AlWadi.person_asset.fk4.

## AlWadi.person_checklistmembers → legacy_alwadi_person_checklistmembers

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Member_ID` | `int NOT NULL` |
| `Opinion` | `int NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `NameNotes` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `RoleNotes` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Location` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` |
| `Person_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12680 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_checklistmembers.fk1, AlWadi.person_checklistmembers.fk2.

## AlWadi.person_course → legacy_alwadi_person_course

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=808 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_course.fk1, AlWadi.person_course.fk2.

## AlWadi.person_education → legacy_alwadi_person_education

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1660 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_education.fk1, AlWadi.person_education.fk2, AlWadi.person_education.fk3.

## AlWadi.person_family → legacy_alwadi_person_family

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Family_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Relation_ID` | `int DEFAULT NULL` |
| `EducationLevel_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `HasWork` | `int DEFAULT '-1'` |
| `P_Provider_ID` | `int DEFAULT NULL` |
| `Work_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `PF_ID` | `int NOT NULL` |

Source PK components: `PF_ID`.
Source indexes: `PRIMARY KEY (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=12082 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_family.fk1, AlWadi.person_family.fk2, AlWadi.person_family.fk3, AlWadi.person_family.fk4, AlWadi.person_family.fk5, AlWadi.person_family.fk6.

## AlWadi.person_goals → legacy_alwadi_person_goals

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=577 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_goals.fk1, AlWadi.person_goals.fk2.

## AlWadi.person_help → legacy_alwadi_person_help

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_help.fk1, AlWadi.person_help.fk2, AlWadi.person_help.fk3, AlWadi.person_help.fk4.

## AlWadi.person_illness → legacy_alwadi_person_illness

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Illness_ID` | `int DEFAULT NULL` |
| `Has_MedicalAid` | `int NOT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Donor` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `AidPeriod_ID` | `int DEFAULT '0'` |
| `MedicalNeeds_ID` | `int DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=402 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_illness.fk1, AlWadi.person_illness.fk2, AlWadi.person_illness.fk3, AlWadi.person_illness.fk4, AlWadi.person_illness.fk5.

## AlWadi.person_loss → legacy_alwadi_person_loss

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1325 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_loss.fk1, AlWadi.person_loss.fk2.

## AlWadi.person_microproject → legacy_alwadi_person_microproject

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `PMP_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PersonType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `P_Loss` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_Goal` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_GoalHow` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_OtherNotes` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_HasWork` | `int NOT NULL DEFAULT '-1'` |
| `P_WorkYears` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_IsLivingWithFamily` | `int NOT NULL DEFAULT '-1'` |
| `P_LiveWithFamily_Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_HasEducation` | `int NOT NULL DEFAULT '-1'` |
| `P_HasCourses` | `int NOT NULL DEFAULT '-1'` |
| `P_HasLoss` | `int NOT NULL DEFAULT '-1'` |
| `P_HasIllness` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAssets` | `int NOT NULL DEFAULT '-1'` |
| `P_HasAidSource` | `int NOT NULL DEFAULT '-1'` |
| `P_HasHelp` | `int NOT NULL DEFAULT '-1'` |
| `P_HasDebts` | `int NOT NULL DEFAULT '-1'` |
| `P_DebtsAmount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_DebtsNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `P_AidSourceNote` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |

Source PK components: `PMP_ID`.
Source indexes: `PRIMARY KEY (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2795 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_microproject.fk1, AlWadi.person_microproject.fk2.

## AlWadi.person_powersource → legacy_alwadi_person_powersource

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1031 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_powersource.fk1, AlWadi.person_powersource.fk2.

## AlWadi.person_workexp → legacy_alwadi_person_workexp

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Work_ID` | `int DEFAULT NULL` |
| `W_Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_WorkType_ID` | `int DEFAULT NULL` |
| `W_BeginDate` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_EndDate` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Place` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_PlaceState` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Status` | `char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_Contact` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_CauseOfLoseDescription` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `W_Salary` | `double DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=6826 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.person_workexp.fk1, AlWadi.person_workexp.fk2, AlWadi.person_workexp.fk3, AlWadi.person_workexp.fk4.

## AlWadi.personstate → legacy_alwadi_personstate

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.powersource → legacy_alwadi_powersource

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.priest → legacy_alwadi_priest

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Priest_ID`.
Source indexes: `PRIMARY KEY (`Priest_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.profession_meeting → legacy_alwadi_profession_meeting

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Career_ID` | `int NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |
| `MeetingDate` | `date NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `InvitedCount` | `smallint NOT NULL DEFAULT '0'` |
| `AttendanceCount` | `smallint NOT NULL DEFAULT '0'` |
| `AbsenceCount` | `smallint NOT NULL DEFAULT '0'` |
| `PaperRegistrar_UserID` | `int DEFAULT NULL` |
| `ProgramRegistrar_UserID` | `int NOT NULL` |
| `Created_At` | `datetime NOT NULL` |
| `Edited_By_UserID` | `int DEFAULT NULL` |
| `Updated_At` | `datetime DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting.fk1, AlWadi.profession_meeting.fk2, AlWadi.profession_meeting.fk3, AlWadi.profession_meeting.fk4, AlWadi.profession_meeting.fk5.

## AlWadi.profession_meeting_answer → legacy_alwadi_profession_meeting_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_answer.fk1, AlWadi.profession_meeting_answer.fk2.

## AlWadi.profession_meeting_attendee → legacy_alwadi_profession_meeting_attendee

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_attendee.fk1, AlWadi.profession_meeting_attendee.fk2, AlWadi.profession_meeting_attendee.fk3.

## AlWadi.profession_meeting_brand → legacy_alwadi_profession_meeting_brand

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_brand.fk1.

## AlWadi.profession_meeting_challenge → legacy_alwadi_profession_meeting_challenge

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_challenge.fk1.

## AlWadi.profession_meeting_competition → legacy_alwadi_profession_meeting_competition

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_competition.fk1.

## AlWadi.profession_meeting_equipment → legacy_alwadi_profession_meeting_equipment

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_equipment.fk1.

## AlWadi.profession_meeting_minute → legacy_alwadi_profession_meeting_minute

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_minute.fk1, AlWadi.profession_meeting_minute.fk2.

## AlWadi.profession_meeting_option_answer → legacy_alwadi_profession_meeting_option_answer

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_option_answer.fk1, AlWadi.profession_meeting_option_answer.fk2.

## AlWadi.profession_meeting_participant → legacy_alwadi_profession_meeting_participant

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Notes` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_participant.fk1, AlWadi.profession_meeting_participant.fk2, AlWadi.profession_meeting_participant.fk3, AlWadi.profession_meeting_participant.fk4.

## AlWadi.profession_meeting_season → legacy_alwadi_profession_meeting_season

Profession meeting workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_meeting_season.fk1.

## AlWadi.profession_option → legacy_alwadi_profession_option

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_option.fk1.

## AlWadi.profession_question → legacy_alwadi_profession_question

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: AlWadi.profession_question.fk1.

## AlWadi.profession_section → legacy_alwadi_profession_section

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;`.
Relationship mappings: none declared.

## AlWadi.purchasing → legacy_alwadi_purchasing

Finance/execution transaction

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `ItemName` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Brand` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ItemCategory_ID` | `int DEFAULT NULL` |
| `Quantity` | `int NOT NULL` |
| `Price` | `decimal(12,2) NOT NULL` |
| `Currency` | `enum('USD','SYP') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `InvoiceDate` | `date DEFAULT NULL` |
| `InvoiceNumber` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `TraderName` | `varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.purchasing.fk1, AlWadi.purchasing.fk2.

## AlWadi.purchasingcategory → legacy_alwadi_purchasingcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.rehabanswer → legacy_alwadi_rehabanswer

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.rehabanswer.fk1.

## AlWadi.rehabquestion → legacy_alwadi_rehabquestion

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.rehabvisit → legacy_alwadi_rehabvisit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans8` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans9` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans10` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans11` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans12` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans13` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans14` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans15` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans8_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans9_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans10_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans11_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans12_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans13_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans14_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans15_Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.rehabvisit.fk1.

## AlWadi.relation → legacy_alwadi_relation

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.resultstable → legacy_alwadi_resultstable

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `رقم المشروع` | `int NOT NULL DEFAULT '0'` |
| `حالة الشراكة` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` |
| `نوع التمويل` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `نوع المشروع` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `تفصيل نوع المشروع` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `الصنف` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `المهنة` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `الصورة الشخصية` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `رسالة الرفض` | `int NOT NULL DEFAULT '0'` |
| `زيارة المنزل` | `int NOT NULL DEFAULT '0'` |
| `تاريخ الزيارة` | `date DEFAULT NULL` |
| `توقيت الزيارة` | `varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `MP_VisitNotes` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `ملاحظات عن الزيارة` | `varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` |
| `هل تم تبديل المشروع` | `int NOT NULL DEFAULT '0'` |
| `اسم المشروع` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `المبلغ المطلوب` | `double NOT NULL` |
| `تاريخ تعبئة الطلب` | `date NOT NULL` |
| `مدة تنفيذ المشروع` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `رقم المستفيد` | `int DEFAULT '0'` |
| `اسم المستفيد` | `varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `اسم الأب` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `اسم الأم` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الجنس` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الحالة الاجتماعية` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الحالة` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `الرقم الوطني` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `المواليد` | `int DEFAULT NULL` |
| `خدمة العلم` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `السبب` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الهيئة الوسيطة` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `الطائفة` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `الكاهن المحلي` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `رقم الجوال` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `رقم الأرضي` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `الأمانة` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `منطقة المنزل` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `العنوان` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `تسكن مع عائلة أخرى` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `من هم` | `varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `مكان السكن` | `mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `ملاحظة عن مكان السكن` | `varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `أملاك أخرى` | `varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `ملاحظة عن أملاك أخرى` | `mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `عدد أفراد المنزل` | `int DEFAULT NULL` |
| `رقم دفتر العائلة` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `أفراد العائلة` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `العمل الحالي` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `العمل السابق` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `التحصيل العلمي` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `الدورات المتبعة` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `ملاحظات عن الدورات` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `هل أنت المعيل الوحيد` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `المعيلين في المنزل` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `المصادر المساعدة` | `varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `مصادر مساعدة اخرى` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |
| `هل يوجد مصادر طبية` | `int DEFAULT NULL` |
| `المصادر الطبية` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `حالة مرضية` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `خسارات` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `ملاحظة عن الخسارات` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin` |
| `وصف المشروع` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `استفادة من برامج سابقة` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الجهات السابقة` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `تحليل المشروع` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `هل يحتاج تراخيص` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الترخيص` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `هل يوجد منافسة` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `المنافسون` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `تأمين اللوازم` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الموردون` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `المخاطر` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin` |
| `ملاحظات عن المخاطر` | `tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `آلية الوقاية` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `معلومات إضافية` | `longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `استراتيجية التسويق` | `mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الربح الصافي` | `mediumint NOT NULL` |
| `ملاحظات عن الربح` | `mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `هل المشروع هو الدخل الوحيد` | `varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` |
| `ملاحظات عن الدخل` | `varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `هل تلتزم بدفتر حسابات` | `varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL` |
| `الميزانية` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci` |
| `مسجل الاستمارة` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `مدخل الاستمارة` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `المعدّل على الاستمارة` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL` |
| `حالة المشروع` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci` |
| `تاريخ تبديل الحالة` | `date DEFAULT NULL` |
| `الممول` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `المجموعة` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.risk → legacy_alwadi_risk

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.role → legacy_alwadi_role

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `Role_ID`.
Source indexes: `PRIMARY KEY (`Role_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.score → legacy_alwadi_score

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.scorevalue → legacy_alwadi_scorevalue

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.scorevalue.fk1.

## AlWadi.specialcase → legacy_alwadi_specialcase

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.specialcase.fk1, AlWadi.specialcase.fk2, AlWadi.specialcase.fk3.

## AlWadi.specialcasetype → legacy_alwadi_specialcasetype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.state → legacy_alwadi_state

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.statereason → legacy_alwadi_statereason

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.street → legacy_alwadi_street

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.subcategory → legacy_alwadi_subcategory

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.subcategory.fk1.

## AlWadi.t1 → legacy_alwadi_t1

Uncertain/auxiliary; retain, investigate before disposition

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Source PK components: none (ordinal identity required).
Source indexes: none.
Table charset/engine evidence: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.task → legacy_alwadi_task

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `T_ID`.
Source indexes: `PRIMARY KEY (`T_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.task_microproject → legacy_alwadi_task_microproject

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Source PK components: `TMP_ID`.
Source indexes: `PRIMARY KEY (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=44437 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.task_microproject.fk1, AlWadi.task_microproject.fk2.

## AlWadi.task_user → legacy_alwadi_task_user

Staff task workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `TU_ID`.
Source indexes: `PRIMARY KEY (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.task_user.fk1, AlWadi.task_user.fk2.

## AlWadi.user → legacy_alwadi_user

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `UserID` | `int NOT NULL` |
| `UserName` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Password` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Email` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT ''` |
| `UserRoleID` | `int NOT NULL` |
| `IsVisitor` | `int NOT NULL` |
| `IsME` | `int NOT NULL` |
| `IsCommunication` | `int NOT NULL` |
| `IsCashier` | `int NOT NULL DEFAULT '0'` |
| `ProfilePicture` | `mediumblob` |

Source PK components: `UserID`.
Source indexes: `PRIMARY KEY (`UserID`)`; `KEY `FK_User_Role` (`UserRoleID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.user.fk1.

## AlWadi.user_notification → legacy_alwadi_user_notification

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Body` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Seen` | `int NOT NULL` |
| `P_Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Sender_ID` | `int DEFAULT NULL` |
| `Pay_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=22901 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.user_notification.fk1, AlWadi.user_notification.fk2, AlWadi.user_notification.fk3.

## AlWadi.version → legacy_alwadi_version

Identity/configuration/audit/notification

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Source PK components: `id`.
Source indexes: `PRIMARY KEY (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.visit → legacy_alwadi_visit

Core/workflow record

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Kind` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Ans1` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans2` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans3` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans4` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans5` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans6` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans7` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Indicators` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Ans1_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans2_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans3_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans4_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans5_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans6_Value` | `int NOT NULL DEFAULT '-1'` |
| `Ans7_Value` | `int NOT NULL DEFAULT '-1'` |
| `VehicleNumber` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.visit.fk1.

## AlWadi.visit_user → legacy_alwadi_visit_user

Relationship/detail workflow

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=1589 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.visit_user.fk1, AlWadi.visit_user.fk2.

## AlWadi.visitanswers → legacy_alwadi_visitanswers

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: AlWadi.visitanswers.fk1.

## AlWadi.visitquestions → legacy_alwadi_visitquestions

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.work → legacy_alwadi_work

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `W_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Source PK components: `W_ID`.
Source indexes: `PRIMARY KEY (`W_ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.

## AlWadi.worktype → legacy_alwadi_worktype

Reference/lookup/questionnaire definition; retain source-qualified

Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.

| Original and target column | Target definition |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Source PK components: `ID`.
Source indexes: `PRIMARY KEY (`ID`)`.
Table charset/engine evidence: `InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;`.
Relationship mappings: none declared.
