# P1.2 physical schema: preservation-layer proposal

This is a concrete lossless compatibility-layer proposal, not an accepted final operational schema. All 127 names / 616 city-table instances retain typed columns in one database. It avoids coercing differing source types or merging source identities. The cost is 616 compatibility tables plus support tables; source-specific query routing and later unified operational projections are still required. An alternative 127-table union design needs explicit treatment of every type, collation and uniqueness variant. This document does not silently choose that tradeoff for the user.

No Laravel migration code or target tables have been created. Restored profiling, independent review, and user confirmation remain gates. See schema-support.md and mapping-spec.md for the supporting physical tables and identity rules.

## Common physical columns and constraints

Every typed compatibility table adds `record_id BIGINT UNSIGNED NOT NULL PRIMARY KEY`, `origin_source_id BIGINT UNSIGNED NOT NULL`, and `origin_table_id BIGINT UNSIGNED NOT NULL`. Require CHECK constants for the registered source/table pair and composite FK `(origin_source_id, origin_table_id, record_id)` to `source_records(source_id, table_id, id)` with RESTRICT updates/deletes. Index that composite for FK enforcement. Every original column is retained under its exact name/type/nullability/default/collation; remove only AUTO_INCREMENT from original columns. Original PK becomes a UNIQUE index (retaining NOT NULL). Retain original UNIQUE and secondary indexes. Original FKs become recorded source contracts plus resolved relationship-ledger rows: they are not enforced against dirty raw values. Never copy destructive CASCADE actions into the target.

The source DDL below is an exact source contract, NOT target executable DDL. Column target definitions are enumerated explicitly in schema-design.json. Target engine: InnoDB. Per-table source charset/collation retained; target server/version and row-format compatibility need validation. No archive-only exclusion is approved: all rows remain queryable through the compatibility layer, but operational workflow coverage is not certified.

## `aganswer`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_aganswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.

Relationship contracts: `FK_AgQuestion`: (Question_ID) → `legacy_a_agquestion` (ID), via ledger.

### Damascus → `legacy_d_aganswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.

Relationship contracts: `FK_AgQuestion`: (Question_ID) → `legacy_d_agquestion` (ID), via ledger.

### Homs → `legacy_h_aganswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.

Relationship contracts: `FK_AgQuestion`: (Question_ID) → `legacy_h_agquestion` (ID), via ledger.

### Latakia → `legacy_l_aganswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.

Relationship contracts: `FK_AgQuestion`: (Question_ID) → `legacy_l_agquestion` (ID), via ledger.

### AlWadi → `legacy_w_aganswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_AgQuestion` (`Question_ID`)`.

Relationship contracts: `FK_AgQuestion`: (Question_ID) → `legacy_w_agquestion` (ID), via ledger.

## `age`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_age`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_age`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_age`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_age`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_age`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `isActive` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `agquestion`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_agquestion`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_agquestion`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_agquestion`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_agquestion`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_agquestion`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `aid`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_aid`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_aid`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_aid`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_aid`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_aid`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

## `aidperiod`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_aidperiod`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_aidperiod`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_aidperiod`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_aidperiod`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_aidperiod`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

## `aidsource`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_aidsource`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_aidsource`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_aidsource`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_aidsource`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_aidsource`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

## `asset`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_asset`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_asset`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_asset`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_asset`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_asset`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `assetbenefits`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_assetbenefits`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_assetbenefits`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_assetbenefits`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_assetbenefits`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_assetbenefits`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

## `assettype`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_assettype`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_assettype`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_assettype`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_assettype`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_assettype`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HomeMark` | `int NOT NULL` |
| `ProjectMark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `backgrounds`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_backgrounds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_backgrounds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_backgrounds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_backgrounds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_backgrounds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Image` | `longblob` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `career`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_career`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.

Relationship contracts: `FK_SubCategory_Career`: (SubCategory_ID) → `legacy_a_subcategory` (ID), via ledger.

### Damascus → `legacy_d_career`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.

Relationship contracts: `FK_SubCategory_Career`: (SubCategory_ID) → `legacy_d_subcategory` (ID), via ledger.

### Homs → `legacy_h_career`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.

Relationship contracts: `FK_SubCategory_Career`: (SubCategory_ID) → `legacy_h_subcategory` (ID), via ledger.

### Latakia → `legacy_l_career`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.

Relationship contracts: `FK_SubCategory_Career`: (SubCategory_ID) → `legacy_l_subcategory` (ID), via ledger.

### AlWadi → `legacy_w_career`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_SubCategory_Career` (`SubCategory_ID`)`.

Relationship contracts: `FK_SubCategory_Career`: (SubCategory_ID) → `legacy_w_subcategory` (ID), via ledger.

## `category`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_category`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_category`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_category`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_category`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_category`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `checklist`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_checklist`

Identity: typed PK tuple. Columns: 40. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.

Relationship contracts: `FK_checklist1`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `FK_checklist2`: (HVisit_Visitor1_ID) → `legacy_a_checklist_member` (ID), via ledger; `FK_checklist3`: (HVisit_Visitor2_ID) → `legacy_a_checklist_member` (ID), via ledger; `FK_checklist4`: (PVisit_Visitor1_ID) → `legacy_a_checklist_member` (ID), via ledger; `FK_checklist5`: (PVisit_Visitor2_ID) → `legacy_a_checklist_member` (ID), via ledger; `FK_checklist6`: (ProTest_User_ID) → `legacy_a_checklist_member` (ID), via ledger; `FK_checklist7`: (RejectMsgSender_ID) → `legacy_a_user` (UserID), via ledger.

### Damascus → `legacy_d_checklist`

Identity: typed PK tuple. Columns: 40. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.

Relationship contracts: `FK_checklist1`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `FK_checklist2`: (HVisit_Visitor1_ID) → `legacy_d_checklist_member` (ID), via ledger; `FK_checklist3`: (HVisit_Visitor2_ID) → `legacy_d_checklist_member` (ID), via ledger; `FK_checklist4`: (PVisit_Visitor1_ID) → `legacy_d_checklist_member` (ID), via ledger; `FK_checklist5`: (PVisit_Visitor2_ID) → `legacy_d_checklist_member` (ID), via ledger; `FK_checklist6`: (ProTest_User_ID) → `legacy_d_checklist_member` (ID), via ledger; `FK_checklist7`: (RejectMsgSender_ID) → `legacy_d_user` (UserID), via ledger.

### Homs → `legacy_h_checklist`

Identity: typed PK tuple. Columns: 40. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.

Relationship contracts: `FK_checklist1`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `FK_checklist2`: (HVisit_Visitor1_ID) → `legacy_h_checklist_member` (ID), via ledger; `FK_checklist3`: (HVisit_Visitor2_ID) → `legacy_h_checklist_member` (ID), via ledger; `FK_checklist4`: (PVisit_Visitor1_ID) → `legacy_h_checklist_member` (ID), via ledger; `FK_checklist5`: (PVisit_Visitor2_ID) → `legacy_h_checklist_member` (ID), via ledger; `FK_checklist6`: (ProTest_User_ID) → `legacy_h_checklist_member` (ID), via ledger; `FK_checklist7`: (RejectMsgSender_ID) → `legacy_h_user` (UserID), via ledger.

### Latakia → `legacy_l_checklist`

Identity: typed PK tuple. Columns: 40. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.

Relationship contracts: `FK_checklist1`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `FK_checklist2`: (HVisit_Visitor1_ID) → `legacy_l_checklist_member` (ID), via ledger; `FK_checklist3`: (HVisit_Visitor2_ID) → `legacy_l_checklist_member` (ID), via ledger; `FK_checklist4`: (PVisit_Visitor1_ID) → `legacy_l_checklist_member` (ID), via ledger; `FK_checklist5`: (PVisit_Visitor2_ID) → `legacy_l_checklist_member` (ID), via ledger; `FK_checklist6`: (ProTest_User_ID) → `legacy_l_checklist_member` (ID), via ledger; `FK_checklist7`: (RejectMsgSender_ID) → `legacy_l_user` (UserID), via ledger.

### AlWadi → `legacy_w_checklist`

Identity: typed PK tuple. Columns: 40. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_checklist1` (`Person_ID`)`; `KEY `FK_checklist2` (`HVisit_Visitor1_ID`)`; `KEY `FK_checklist3` (`HVisit_Visitor2_ID`)`; `KEY `FK_checklist4` (`PVisit_Visitor1_ID`)`; `KEY `FK_checklist5` (`PVisit_Visitor2_ID`)`; `KEY `FK_checklist6` (`ProTest_User_ID`)`; `KEY `FK_checklist7` (`RejectMsgSender_ID`)`.

Relationship contracts: `FK_checklist1`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `FK_checklist2`: (HVisit_Visitor1_ID) → `legacy_w_checklist_member` (ID), via ledger; `FK_checklist3`: (HVisit_Visitor2_ID) → `legacy_w_checklist_member` (ID), via ledger; `FK_checklist4`: (PVisit_Visitor1_ID) → `legacy_w_checklist_member` (ID), via ledger; `FK_checklist5`: (PVisit_Visitor2_ID) → `legacy_w_checklist_member` (ID), via ledger; `FK_checklist6`: (ProTest_User_ID) → `legacy_w_checklist_member` (ID), via ledger; `FK_checklist7`: (RejectMsgSender_ID) → `legacy_w_user` (UserID), via ledger.

## `checklist_member`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_checklist_member`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.

Relationship contracts: `FK_Position_ID`: (Position_ID) → `legacy_a_checklist_position` (ID), via ledger.

### Damascus → `legacy_d_checklist_member`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.

Relationship contracts: `FK_Position_ID`: (Position_ID) → `legacy_d_checklist_position` (ID), via ledger.

### Homs → `legacy_h_checklist_member`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.

Relationship contracts: `FK_Position_ID`: (Position_ID) → `legacy_h_checklist_position` (ID), via ledger.

### Latakia → `legacy_l_checklist_member`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.

Relationship contracts: `FK_Position_ID`: (Position_ID) → `legacy_l_checklist_position` (ID), via ledger.

### AlWadi → `legacy_w_checklist_member`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Position_ID` | `int DEFAULT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Position_ID` (`Position_ID`)`.

Relationship contracts: `FK_Position_ID`: (Position_ID) → `legacy_w_checklist_position` (ID), via ledger.

## `checklist_opinion`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_checklist_opinion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_checklist_opinion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_checklist_opinion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_checklist_opinion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_checklist_opinion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Index` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `checklist_position`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_checklist_position`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_checklist_position`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_checklist_position`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_checklist_position`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_checklist_position`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `course`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_course`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_course`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_course`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_course`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_course`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `databases`

Identity/configuration/audit/notification; disposition: retain every source column and row.

### Aleppo → `legacy_a_databases`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_databases`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_databases`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_databases`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_databases`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `name` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `center` | `varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ftp_path` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`.

Relationship contracts: No declared source foreign keys..

## `dept`

Uncertain/auxiliary; retain, investigate before disposition; disposition: retain every source column and row.

### Damascus → `legacy_d_dept`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `PMP_ID` | `int DEFAULT NULL` |
| `P_HasDebts` | `int DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

## `donor`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_donor`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_donor`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_donor`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_donor`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_donor`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_abb` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `donorgroup`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_donorgroup`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.

Relationship contracts: `FK_Donor_Group`: (Donor_ID) → `legacy_a_donor` (ID), via ledger.

### Damascus → `legacy_d_donorgroup`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.

Relationship contracts: `FK_Donor_Group`: (Donor_ID) → `legacy_d_donor` (ID), via ledger.

### Homs → `legacy_h_donorgroup`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.

Relationship contracts: `FK_Donor_Group`: (Donor_ID) → `legacy_h_donor` (ID), via ledger.

### Latakia → `legacy_l_donorgroup`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.

Relationship contracts: `FK_Donor_Group`: (Donor_ID) → `legacy_l_donor` (ID), via ledger.

### AlWadi → `legacy_w_donorgroup`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Donor_Group` (`Donor_ID`)`.

Relationship contracts: `FK_Donor_Group`: (Donor_ID) → `legacy_w_donor` (ID), via ledger.

## `education`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `educationlevel`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_educationlevel`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_educationlevel`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_educationlevel`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_educationlevel`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_educationlevel`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `educationtype`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_educationtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_educationtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_educationtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_educationtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_educationtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `exefile`

Finance/execution transaction; disposition: retain every source column and row.

### Aleppo → `legacy_a_exefile`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_ExeFile_MicroProject`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_exefile`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_ExeFile_MicroProject`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_exefile`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_ExeFile_MicroProject`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_exefile`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_ExeFile_MicroProject`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_exefile`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ExeF_ID` | `int NOT NULL` |
| `ExeF_No` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_BeginDate` | `date DEFAULT NULL` |
| `ExeF_CurrentDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundDate` | `date DEFAULT NULL` |
| `ExeF_ImpoundType` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ExeF_NumOfMonths` | `int NOT NULL DEFAULT '5'` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ExeF_ID`)`; `KEY `FK_ExeFile_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_ExeFile_MicroProject`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `family`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_family`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`F_ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_family`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`F_ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_family`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`F_ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_family`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`F_ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_family`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `F_ID` | `int NOT NULL` |
| `F_Number` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`F_ID`)`.

Relationship contracts: No declared source foreign keys..

## `file`

Attachment metadata/BLOB; disposition: retain every source column and row.

### Aleppo → `legacy_a_file`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_File_MicroProject`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_file`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_File_MicroProject`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_file`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_File_MicroProject`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_file`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_File_MicroProject`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_file`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `File_ID` | `int NOT NULL` |
| `File_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `File_Content` | `longblob NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`File_ID`)`; `KEY `FK_File_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_File_MicroProject`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `fundtype`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_fundtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_fundtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_fundtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_fundtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_fundtype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `goal`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_goal`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_goal`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_goal`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_goal`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_goal`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `illness`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_illness`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_illness`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_illness`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_illness`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_illness`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `image`

Attachment metadata/BLOB; disposition: retain every source column and row.

### Aleppo → `legacy_a_image`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.

Relationship contracts: `FK_Image_ImageType`: (ImageType_ID) → `legacy_a_imagetype` (ID), via ledger; `FK_Image_MicroProject`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_Image_Person`: (Person_ID) → `legacy_a_person` (P_ID), via ledger.

### Damascus → `legacy_d_image`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.

Relationship contracts: `FK_Image_ImageType`: (ImageType_ID) → `legacy_d_imagetype` (ID), via ledger; `FK_Image_MicroProject`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_Image_Person`: (Person_ID) → `legacy_d_person` (P_ID), via ledger.

### Homs → `legacy_h_image`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.

Relationship contracts: `FK_Image_ImageType`: (ImageType_ID) → `legacy_h_imagetype` (ID), via ledger; `FK_Image_MicroProject`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_Image_Person`: (Person_ID) → `legacy_h_person` (P_ID), via ledger.

### Latakia → `legacy_l_image`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.

Relationship contracts: `FK_Image_ImageType`: (ImageType_ID) → `legacy_l_imagetype` (ID), via ledger; `FK_Image_MicroProject`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_Image_Person`: (Person_ID) → `legacy_l_person` (P_ID), via ledger.

### AlWadi → `legacy_w_image`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Image_ID` | `int NOT NULL` |
| `image_NewPath` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ImageType_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Image_ID`)`; `KEY `FK_Image_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Image_ImageType` (`ImageType_ID`)`; `KEY `FK_Image_Person` (`Person_ID`)`.

Relationship contracts: `FK_Image_ImageType`: (ImageType_ID) → `legacy_w_imagetype` (ID), via ledger; `FK_Image_MicroProject`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_Image_Person`: (Person_ID) → `legacy_w_person` (P_ID), via ledger.

## `imagetype`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_imagetype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_imagetype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_imagetype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_imagetype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_imagetype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `incomesource`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_incomesource`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_incomesource`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_incomesource`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_incomesource`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_incomesource`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `language`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_language`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`L_ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_language`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`L_ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_language`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`L_ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_language`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`L_ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_language`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `L_ID` | `int NOT NULL` |
| `L_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`L_ID`)`.

Relationship contracts: No declared source foreign keys..

## `level`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_level`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Level_ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_level`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Level_ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_level`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Level_ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_level`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Level_ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_level`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Level_ID` | `int NOT NULL` |
| `Level_Symbol` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Level_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Level_ID`)`.

Relationship contracts: No declared source foreign keys..

## `loan`

Finance/execution transaction; disposition: retain every source column and row.

### Aleppo → `legacy_a_loan`

Identity: typed PK tuple. Columns: 12. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_loan`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_loan`

Identity: typed PK tuple. Columns: 12. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_loan`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_loan`

Identity: typed PK tuple. Columns: 12. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_loan`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_loan`

Identity: typed PK tuple. Columns: 12. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_loan`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_loan`

Identity: typed PK tuple. Columns: 12. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`Loan_ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_loan`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `log`

Identity/configuration/audit/notification; disposition: retain every source column and row.

### Aleppo → `legacy_a_log`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Log_ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_log`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Log_ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_log`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Log_ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_log`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Log_ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_log`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Log_ID` | `int NOT NULL` |
| `Log_Type` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_OnTable` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_User` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Log_Date` | `datetime DEFAULT NULL` |
| `Log_Seen` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Log_ID`)`.

Relationship contracts: No declared source foreign keys..

## `loss`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_loss`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_loss`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_loss`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_loss`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_loss`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `manualfile`

Attachment metadata/BLOB; disposition: retain every source column and row.

### Aleppo → `legacy_a_manualfile`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_manualfile`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_manualfile`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_manualfile`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_manualfile`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Content` | `longblob` |
| `Path` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `maritalstatus`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_maritalstatus`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_maritalstatus`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_maritalstatus`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_maritalstatus`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_maritalstatus`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `material`

Finance/execution transaction; disposition: retain every source column and row.

### Aleppo → `legacy_a_material`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Material_MicroProject`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_material`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Material_MicroProject`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_material`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Material_MicroProject`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_material`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Material_MicroProject`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_material`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `double NOT NULL` |
| `Price` | `double NOT NULL` |
| `LocalContribution` | `double NOT NULL` |
| `Comments` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Material_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Material_MicroProject`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `meanswer`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_meanswer`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.

Relationship contracts: `FK_question_answer`: (Question_ID) → `legacy_a_mequestion` (ID), via ledger.

### Damascus → `legacy_d_meanswer`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.

Relationship contracts: `FK_question_answer`: (Question_ID) → `legacy_d_mequestion` (ID), via ledger.

### Homs → `legacy_h_meanswer`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.

Relationship contracts: `FK_question_answer`: (Question_ID) → `legacy_h_mequestion` (ID), via ledger.

### Latakia → `legacy_l_meanswer`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.

Relationship contracts: `FK_question_answer`: (Question_ID) → `legacy_l_mequestion` (ID), via ledger.

### AlWadi → `legacy_w_meanswer`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_question_answer` (`Question_ID`)`.

Relationship contracts: `FK_question_answer`: (Question_ID) → `legacy_w_mequestion` (ID), via ledger.

## `medicalneeds`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_medicalneeds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_medicalneeds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_medicalneeds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_medicalneeds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_medicalneeds`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

## `meprofitmarks`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_meprofitmarks`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_meprofitmarks`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_meprofitmarks`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_meprofitmarks`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_meprofitmarks`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `float DEFAULT NULL` |
| `Max` | `float DEFAULT NULL` |
| `Mark` | `int NOT NULL` |
| `IsActive` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `mequestion`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_mequestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_mequestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_mequestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_mequestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_mequestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `meresultranges`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_meresultranges`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_meresultranges`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_meresultranges`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_meresultranges`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_meresultranges`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Min` | `int NOT NULL` |
| `Max` | `int NOT NULL` |
| `Evaluation` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Evaluation_en` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `mevisit`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_mevisit`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.

Relationship contracts: `FK_Person_meVisit`: (Person_ID) → `legacy_a_person` (P_ID), via ledger.

### Damascus → `legacy_d_mevisit`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.

Relationship contracts: `FK_Person_meVisit`: (Person_ID) → `legacy_d_person` (P_ID), via ledger.

### Homs → `legacy_h_mevisit`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.

Relationship contracts: `FK_Person_meVisit`: (Person_ID) → `legacy_h_person` (P_ID), via ledger.

### Latakia → `legacy_l_mevisit`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.

Relationship contracts: `FK_Person_meVisit`: (Person_ID) → `legacy_l_person` (P_ID), via ledger.

### AlWadi → `legacy_w_mevisit`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_meVisit` (`Person_ID`)`.

Relationship contracts: `FK_Person_meVisit`: (Person_ID) → `legacy_w_person` (P_ID), via ledger.

## `mevisit_answer`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_mevisit_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.

Relationship contracts: `FK_VQA_answer`: (Answer_ID) → `legacy_a_meanswer` (ID), via ledger; `FK_VQA_qustion`: (Question_ID) → `legacy_a_mequestion` (ID), via ledger; `FK_VQA_visit`: (Visit_ID) → `legacy_a_mevisit` (ID), via ledger.

### Damascus → `legacy_d_mevisit_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.

Relationship contracts: `FK_VQA_answer`: (Answer_ID) → `legacy_d_meanswer` (ID), via ledger; `FK_VQA_qustion`: (Question_ID) → `legacy_d_mequestion` (ID), via ledger; `FK_VQA_visit`: (Visit_ID) → `legacy_d_mevisit` (ID), via ledger.

### Homs → `legacy_h_mevisit_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.

Relationship contracts: `FK_VQA_answer`: (Answer_ID) → `legacy_h_meanswer` (ID), via ledger; `FK_VQA_qustion`: (Question_ID) → `legacy_h_mequestion` (ID), via ledger; `FK_VQA_visit`: (Visit_ID) → `legacy_h_mevisit` (ID), via ledger.

### Latakia → `legacy_l_mevisit_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.

Relationship contracts: `FK_VQA_answer`: (Answer_ID) → `legacy_l_meanswer` (ID), via ledger; `FK_VQA_qustion`: (Question_ID) → `legacy_l_mequestion` (ID), via ledger; `FK_VQA_visit`: (Visit_ID) → `legacy_l_mevisit` (ID), via ledger.

### AlWadi → `legacy_w_mevisit_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Notes` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_VQA_visit` (`Visit_ID`)`; `KEY `FK_VQA_qustion` (`Question_ID`)`; `KEY `FK_VQA_answer` (`Answer_ID`)`.

Relationship contracts: `FK_VQA_answer`: (Answer_ID) → `legacy_w_meanswer` (ID), via ledger; `FK_VQA_qustion`: (Question_ID) → `legacy_w_mequestion` (ID), via ledger; `FK_VQA_visit`: (Visit_ID) → `legacy_w_mevisit` (ID), via ledger.

## `mevisit_user`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_mevisit_user`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.

Relationship contracts: `FK_meVisit_User`: (Visit_ID) → `legacy_a_mevisit` (ID), via ledger; `FK_User_meVisit`: (User_ID) → `legacy_a_user` (UserID), via ledger.

### Damascus → `legacy_d_mevisit_user`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.

Relationship contracts: `FK_meVisit_User`: (Visit_ID) → `legacy_d_mevisit` (ID), via ledger; `FK_User_meVisit`: (User_ID) → `legacy_d_user` (UserID), via ledger.

### Homs → `legacy_h_mevisit_user`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.

Relationship contracts: `FK_meVisit_User`: (Visit_ID) → `legacy_h_mevisit` (ID), via ledger; `FK_User_meVisit`: (User_ID) → `legacy_h_user` (UserID), via ledger.

### Latakia → `legacy_l_mevisit_user`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.

Relationship contracts: `FK_meVisit_User`: (Visit_ID) → `legacy_l_mevisit` (ID), via ledger; `FK_User_meVisit`: (User_ID) → `legacy_l_user` (UserID), via ledger.

### AlWadi → `legacy_w_mevisit_user`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Mark` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_meVisit` (`User_ID`)`; `KEY `FK_meVisit_User` (`Visit_ID`)`.

Relationship contracts: `FK_meVisit_User`: (Visit_ID) → `legacy_w_mevisit` (ID), via ledger; `FK_User_meVisit`: (User_ID) → `legacy_w_user` (UserID), via ledger.

## `microproject`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_microproject`

Identity: typed PK tuple. Columns: 35. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_MP_SubCategory` (`SubCategory_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.

Relationship contracts: `FK_Donor_MicroProject`: (MP_Donor) → `legacy_a_donor` (ID), via ledger; `FK_DonorGroup_MicroProject`: (DonorGroup_ID) → `legacy_a_donorgroup` (ID), via ledger; `FK_FundType_MicroProject`: (MP_FundType_ID) → `legacy_a_fundtype` (ID), via ledger; `FK_MP_Career`: (Career_ID) → `legacy_a_career` (ID), via ledger; `FK_MP_Category`: (Category_ID) → `legacy_a_category` (ID), via ledger; `FK_MP_Street`: (MP_Street_ID) → `legacy_a_street` (ID), via ledger; `FK_MP_SubCategory`: (SubCategory_ID) → `legacy_a_subcategory` (ID), via ledger; `FK_MP_user1`: (MP_PaperUser_ID) → `legacy_a_user` (UserID), via ledger; `FK_MP_user2`: (MP_ProgramUser_ID) → `legacy_a_user` (UserID), via ledger; `FK_MP_user3`: (MP_EditedByUser_ID) → `legacy_a_user` (UserID), via ledger; `FK_PlaceType_MicroProject`: (MP_PlaceType_ID) → `legacy_a_assettype` (ID), via ledger; `FK_SubType_MicroProject`: (MP_SubType_ID) → `legacy_a_microprojectsubtype` (ID), via ledger; `FK_Type_MicroProject`: (MP_Type_ID) → `legacy_a_microprojecttype` (ID), via ledger.

### Damascus → `legacy_d_microproject`

Identity: typed PK tuple. Columns: 35. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_MP_SubCategory` (`SubCategory_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.

Relationship contracts: `FK_Donor_MicroProject`: (MP_Donor) → `legacy_d_donor` (ID), via ledger; `FK_DonorGroup_MicroProject`: (DonorGroup_ID) → `legacy_d_donorgroup` (ID), via ledger; `FK_FundType_MicroProject`: (MP_FundType_ID) → `legacy_d_fundtype` (ID), via ledger; `FK_MP_Career`: (Career_ID) → `legacy_d_career` (ID), via ledger; `FK_MP_Category`: (Category_ID) → `legacy_d_category` (ID), via ledger; `FK_MP_Street`: (MP_Street_ID) → `legacy_d_street` (ID), via ledger; `FK_MP_SubCategory`: (SubCategory_ID) → `legacy_d_subcategory` (ID), via ledger; `FK_MP_user1`: (MP_PaperUser_ID) → `legacy_d_user` (UserID), via ledger; `FK_MP_user2`: (MP_ProgramUser_ID) → `legacy_d_user` (UserID), via ledger; `FK_MP_user3`: (MP_EditedByUser_ID) → `legacy_d_user` (UserID), via ledger; `FK_PlaceType_MicroProject`: (MP_PlaceType_ID) → `legacy_d_assettype` (ID), via ledger; `FK_SubType_MicroProject`: (MP_SubType_ID) → `legacy_d_microprojectsubtype` (ID), via ledger; `FK_Type_MicroProject`: (MP_Type_ID) → `legacy_d_microprojecttype` (ID), via ledger.

### Homs → `legacy_h_microproject`

Identity: typed PK tuple. Columns: 35. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_SubCategory_MicroProject` (`SubCategory_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.

Relationship contracts: `FK_Donor_MicroProject`: (MP_Donor) → `legacy_h_donor` (ID), via ledger; `FK_DonorGroup_MicroProject`: (DonorGroup_ID) → `legacy_h_donorgroup` (ID), via ledger; `FK_FundType_MicroProject`: (MP_FundType_ID) → `legacy_h_fundtype` (ID), via ledger; `FK_MP_Career`: (Career_ID) → `legacy_h_career` (ID), via ledger; `FK_MP_Category`: (Category_ID) → `legacy_h_category` (ID), via ledger; `FK_MP_Street`: (MP_Street_ID) → `legacy_h_street` (ID), via ledger; `FK_MP_SubCategory`: (SubCategory_ID) → `legacy_h_subcategory` (ID), via ledger; `FK_MP_user1`: (MP_PaperUser_ID) → `legacy_h_user` (UserID), via ledger; `FK_MP_user2`: (MP_ProgramUser_ID) → `legacy_h_user` (UserID), via ledger; `FK_MP_user3`: (MP_EditedByUser_ID) → `legacy_h_user` (UserID), via ledger; `FK_PlaceType_MicroProject`: (MP_PlaceType_ID) → `legacy_h_assettype` (ID), via ledger; `FK_SubType_MicroProject`: (MP_SubType_ID) → `legacy_h_microprojectsubtype` (ID), via ledger; `FK_Type_MicroProject`: (MP_Type_ID) → `legacy_h_microprojecttype` (ID), via ledger.

### Latakia → `legacy_l_microproject`

Identity: typed PK tuple. Columns: 35. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_MP_SubCategory` (`SubCategory_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.

Relationship contracts: `FK_Donor_MicroProject`: (MP_Donor) → `legacy_l_donor` (ID), via ledger; `FK_DonorGroup_MicroProject`: (DonorGroup_ID) → `legacy_l_donorgroup` (ID), via ledger; `FK_FundType_MicroProject`: (MP_FundType_ID) → `legacy_l_fundtype` (ID), via ledger; `FK_MP_Career`: (Career_ID) → `legacy_l_career` (ID), via ledger; `FK_MP_Category`: (Category_ID) → `legacy_l_category` (ID), via ledger; `FK_MP_Street`: (MP_Street_ID) → `legacy_l_street` (ID), via ledger; `FK_MP_SubCategory`: (SubCategory_ID) → `legacy_l_subcategory` (ID), via ledger; `FK_MP_user1`: (MP_PaperUser_ID) → `legacy_l_user` (UserID), via ledger; `FK_MP_user2`: (MP_ProgramUser_ID) → `legacy_l_user` (UserID), via ledger; `FK_MP_user3`: (MP_EditedByUser_ID) → `legacy_l_user` (UserID), via ledger; `FK_PlaceType_MicroProject`: (MP_PlaceType_ID) → `legacy_l_assettype` (ID), via ledger; `FK_SubType_MicroProject`: (MP_SubType_ID) → `legacy_l_microprojectsubtype` (ID), via ledger; `FK_Type_MicroProject`: (MP_Type_ID) → `legacy_l_microprojecttype` (ID), via ledger.

### AlWadi → `legacy_w_microproject`

Identity: typed PK tuple. Columns: 35. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`MP_ID`)`; `KEY `FK_Category_MicroProject` (`Category_ID`)`; `KEY `FK_Donor_MicroProject` (`MP_Donor`)`; `KEY `FK_DonorGroup_MicroProject` (`DonorGroup_ID`)`; `KEY `FK_SubCategory_MicroProject` (`SubCategory_ID`)`; `KEY `FK_Type_MicroProject` (`MP_Type_ID`)`; `KEY `FK_SubType_MicroProject` (`MP_SubType_ID`)`; `KEY `FK_FundType_MicroProject` (`MP_FundType_ID`)`; `KEY `FK_MP_Street` (`MP_Street_ID`)`; `KEY `FK_MP_user1` (`MP_PaperUser_ID`)`; `KEY `FK_MP_user2` (`MP_ProgramUser_ID`)`; `KEY `FK_MP_user3` (`MP_EditedByUser_ID`)`; `KEY `FK_MP_Career` (`Career_ID`)`; `KEY `FK_PlaceType_MicroProject` (`MP_PlaceType_ID`)`.

Relationship contracts: `FK_Donor_MicroProject`: (MP_Donor) → `legacy_w_donor` (ID), via ledger; `FK_DonorGroup_MicroProject`: (DonorGroup_ID) → `legacy_w_donorgroup` (ID), via ledger; `FK_FundType_MicroProject`: (MP_FundType_ID) → `legacy_w_fundtype` (ID), via ledger; `FK_MP_Career`: (Career_ID) → `legacy_w_career` (ID), via ledger; `FK_MP_Category`: (Category_ID) → `legacy_w_category` (ID), via ledger; `FK_MP_Street`: (MP_Street_ID) → `legacy_w_street` (ID), via ledger; `FK_MP_SubCategory`: (SubCategory_ID) → `legacy_w_subcategory` (ID), via ledger; `FK_MP_user1`: (MP_PaperUser_ID) → `legacy_w_user` (UserID), via ledger; `FK_MP_user2`: (MP_ProgramUser_ID) → `legacy_w_user` (UserID), via ledger; `FK_MP_user3`: (MP_EditedByUser_ID) → `legacy_w_user` (UserID), via ledger; `FK_PlaceType_MicroProject`: (MP_PlaceType_ID) → `legacy_w_assettype` (ID), via ledger; `FK_SubType_MicroProject`: (MP_SubType_ID) → `legacy_w_microprojectsubtype` (ID), via ledger; `FK_Type_MicroProject`: (MP_Type_ID) → `legacy_w_microprojecttype` (ID), via ledger.

## `microproject_ag_answer`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_microproject_ag_answer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.

Relationship contracts: `FK_ag_micro1`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_microproject_ag_answer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.

Relationship contracts: `FK_ag_micro1`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_microproject_ag_answer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.

Relationship contracts: `FK_ag_micro1`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_microproject_ag_answer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.

Relationship contracts: `FK_ag_micro1`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_microproject_ag_answer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `MicroProject_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Answer_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`MicroProject_ID`,`Question_ID`,`Answer_ID`)`.

Relationship contracts: `FK_ag_micro1`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `microproject_powersource`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_microproject_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_MicroProject_Power1`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_MicroProject_Power2`: (PowerSource_ID) → `legacy_a_powersource` (ID), via ledger.

### Damascus → `legacy_d_microproject_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_MicroProject_Power1`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_MicroProject_Power2`: (PowerSource_ID) → `legacy_d_powersource` (ID), via ledger.

### Homs → `legacy_h_microproject_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_MicroProject_Power1`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_MicroProject_Power2`: (PowerSource_ID) → `legacy_h_powersource` (ID), via ledger.

### Latakia → `legacy_l_microproject_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_MicroProject_Power1`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_MicroProject_Power2`: (PowerSource_ID) → `legacy_l_powersource` (ID), via ledger.

### AlWadi → `legacy_w_microproject_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL` |
| `Responsability` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Power1` (`MicroProject_ID`)`; `KEY `FK_MicroProject_Power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_MicroProject_Power1`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_MicroProject_Power2`: (PowerSource_ID) → `legacy_w_powersource` (ID), via ledger.

## `microproject_rehab`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_microproject_rehab`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_microproject_rehab`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_microproject_rehab`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_microproject_rehab`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_microproject_rehab`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `PropertyOwnership` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `PropertyArea` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Created_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Edited_By` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `MicroProject_ID` (`MicroProject_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `microproject_rehab_answer`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_microproject_rehab_answer`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab1`: (MicroRehab_ID) → `legacy_a_microproject_rehab` (ID), via ledger; `FK_MicroProject_Rehab2`: (Question_ID) → `legacy_a_rehabquestion` (ID), via ledger; `FK_MicroProject_Rehab3`: (Answer_ID) → `legacy_a_rehabanswer` (ID), via ledger.

### Damascus → `legacy_d_microproject_rehab_answer`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab1`: (MicroRehab_ID) → `legacy_d_microproject_rehab` (ID), via ledger; `FK_MicroProject_Rehab2`: (Question_ID) → `legacy_d_rehabquestion` (ID), via ledger; `FK_MicroProject_Rehab3`: (Answer_ID) → `legacy_d_rehabanswer` (ID), via ledger.

### Homs → `legacy_h_microproject_rehab_answer`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab1`: (MicroRehab_ID) → `legacy_h_microproject_rehab` (ID), via ledger; `FK_MicroProject_Rehab2`: (Question_ID) → `legacy_h_rehabquestion` (ID), via ledger; `FK_MicroProject_Rehab3`: (Answer_ID) → `legacy_h_rehabanswer` (ID), via ledger.

### Latakia → `legacy_l_microproject_rehab_answer`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab1`: (MicroRehab_ID) → `legacy_l_microproject_rehab` (ID), via ledger; `FK_MicroProject_Rehab2`: (Question_ID) → `legacy_l_rehabquestion` (ID), via ledger; `FK_MicroProject_Rehab3`: (Answer_ID) → `legacy_l_rehabanswer` (ID), via ledger.

### AlWadi → `legacy_w_microproject_rehab_answer`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroRehab_ID` | `int DEFAULT NULL` |
| `Question_ID` | `int DEFAULT NULL` |
| `Answer_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Number` | `int NOT NULL` |
| `Price` | `double NOT NULL` |
| `Result` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_Rehab1` (`MicroRehab_ID`)`; `KEY `FK_MicroProject_Rehab2` (`Question_ID`)`; `KEY `FK_MicroProject_Rehab3` (`Answer_ID`)`.

Relationship contracts: `FK_MicroProject_Rehab1`: (MicroRehab_ID) → `legacy_w_microproject_rehab` (ID), via ledger; `FK_MicroProject_Rehab2`: (Question_ID) → `legacy_w_rehabquestion` (ID), via ledger; `FK_MicroProject_Rehab3`: (Answer_ID) → `legacy_w_rehabanswer` (ID), via ledger.

## `microproject_risk`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_microproject_risk`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.

Relationship contracts: `FK_microproject_risk1`: (Risk_ID) → `legacy_a_risk` (ID), via ledger; `FK_microproject_risk2`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_microproject_risk`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.

Relationship contracts: `FK_microproject_risk1`: (Risk_ID) → `legacy_d_risk` (ID), via ledger; `FK_microproject_risk2`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_microproject_risk`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.

Relationship contracts: `FK_microproject_risk1`: (Risk_ID) → `legacy_h_risk` (ID), via ledger; `FK_microproject_risk2`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_microproject_risk`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.

Relationship contracts: `FK_microproject_risk1`: (Risk_ID) → `legacy_l_risk` (ID), via ledger; `FK_microproject_risk2`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_microproject_risk`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `Risk_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_risk1` (`Risk_ID`)`; `KEY `FK_microproject_risk2` (`MicroProject_ID`)`.

Relationship contracts: `FK_microproject_risk1`: (Risk_ID) → `legacy_w_risk` (ID), via ledger; `FK_microproject_risk2`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `microproject_score`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_microproject_score`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.

Relationship contracts: `FK_MicroProject_score1`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_MicroProject_score2`: (Score_ID) → `legacy_a_score` (ID), via ledger.

### Damascus → `legacy_d_microproject_score`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.

Relationship contracts: `FK_MicroProject_score1`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_MicroProject_score2`: (Score_ID) → `legacy_d_score` (ID), via ledger.

### Homs → `legacy_h_microproject_score`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.

Relationship contracts: `FK_MicroProject_score1`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_MicroProject_score2`: (Score_ID) → `legacy_h_score` (ID), via ledger.

### Latakia → `legacy_l_microproject_score`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.

Relationship contracts: `FK_MicroProject_score1`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_MicroProject_score2`: (Score_ID) → `legacy_l_score` (ID), via ledger.

### AlWadi → `legacy_w_microproject_score`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Score_ID` | `int DEFAULT NULL` |
| `value` | `int NOT NULL` |
| `notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_MicroProject_score` (`MicroProject_ID`)`; `KEY `FK_MicroProject_score2` (`Score_ID`)`.

Relationship contracts: `FK_MicroProject_score1`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_MicroProject_score2`: (Score_ID) → `legacy_w_score` (ID), via ledger.

## `microproject_state`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_microproject_state`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.

Relationship contracts: `FK_microproject_state1`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_microproject_state2`: (State_ID) → `legacy_a_state` (ID), via ledger; `FK_microproject_state3`: (StateReason_ID) → `legacy_a_statereason` (ID), via ledger.

### Damascus → `legacy_d_microproject_state`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.

Relationship contracts: `FK_microproject_state1`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_microproject_state2`: (State_ID) → `legacy_d_state` (ID), via ledger; `FK_microproject_state3`: (StateReason_ID) → `legacy_d_statereason` (ID), via ledger.

### Homs → `legacy_h_microproject_state`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.

Relationship contracts: `FK_microproject_state1`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_microproject_state2`: (State_ID) → `legacy_h_state` (ID), via ledger; `FK_microproject_state3`: (StateReason_ID) → `legacy_h_statereason` (ID), via ledger.

### Latakia → `legacy_l_microproject_state`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.

Relationship contracts: `FK_microproject_state1`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_microproject_state2`: (State_ID) → `legacy_l_state` (ID), via ledger; `FK_microproject_state3`: (StateReason_ID) → `legacy_l_statereason` (ID), via ledger.

### AlWadi → `legacy_w_microproject_state`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `State_ID` | `int DEFAULT NULL` |
| `StateReason_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `date DEFAULT NULL` |
| `Current_State` | `int NOT NULL DEFAULT '1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microproject_state1` (`MicroProject_ID`)`; `KEY `FK_microproject_state2` (`State_ID`)`; `KEY `FK_microproject_state3` (`StateReason_ID`)`.

Relationship contracts: `FK_microproject_state1`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_microproject_state2`: (State_ID) → `legacy_w_state` (ID), via ledger; `FK_microproject_state3`: (StateReason_ID) → `legacy_w_statereason` (ID), via ledger.

## `microprojectsubtype`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_microprojectsubtype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.

Relationship contracts: `FK_microprojectsubtype`: (Type_ID) → `legacy_a_microprojecttype` (ID), via ledger.

### Damascus → `legacy_d_microprojectsubtype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.

Relationship contracts: `FK_microprojectsubtype`: (Type_ID) → `legacy_d_microprojecttype` (ID), via ledger.

### Homs → `legacy_h_microprojectsubtype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.

Relationship contracts: `FK_microprojectsubtype`: (Type_ID) → `legacy_h_microprojecttype` (ID), via ledger.

### Latakia → `legacy_l_microprojectsubtype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.

Relationship contracts: `FK_microprojectsubtype`: (Type_ID) → `legacy_l_microprojecttype` (ID), via ledger.

### AlWadi → `legacy_w_microprojectsubtype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_microprojectsubtype` (`Type_ID`)`.

Relationship contracts: `FK_microprojectsubtype`: (Type_ID) → `legacy_w_microprojecttype` (ID), via ledger.

## `microprojecttype`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_microprojecttype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_microprojecttype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_microprojecttype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_microprojecttype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_microprojecttype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `militarystate`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_militarystate`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_militarystate`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_militarystate`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_militarystate`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_militarystate`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `office_monitoring`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_office_monitoring`

Identity: typed PK tuple. Columns: 15. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.

Relationship contracts: `FK_Office_Visit`: (Visit_ID) → `legacy_a_visit` (ID), via ledger.

### Damascus → `legacy_d_office_monitoring`

Identity: typed PK tuple. Columns: 15. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.

Relationship contracts: `FK_Office_Visit`: (Visit_ID) → `legacy_d_visit` (ID), via ledger.

### Homs → `legacy_h_office_monitoring`

Identity: typed PK tuple. Columns: 15. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.

Relationship contracts: `FK_Office_Visit`: (Visit_ID) → `legacy_h_visit` (ID), via ledger.

### Latakia → `legacy_l_office_monitoring`

Identity: typed PK tuple. Columns: 15. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.

Relationship contracts: `FK_Office_Visit`: (Visit_ID) → `legacy_l_visit` (ID), via ledger.

### AlWadi → `legacy_w_office_monitoring`

Identity: typed PK tuple. Columns: 15. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Office_Visit` (`Visit_ID`)`.

Relationship contracts: `FK_Office_Visit`: (Visit_ID) → `legacy_w_visit` (ID), via ledger.

## `parish`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_parish`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_parish`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_parish`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_parish`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_parish`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `payment`

Finance/execution transaction; disposition: retain every source column and row.

### Aleppo → `legacy_a_payment`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.

Relationship contracts: `FK_Payment_loan`: (Loan_ID) → `legacy_a_loan` (Loan_ID), via ledger.

### Damascus → `legacy_d_payment`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL DEFAULT '0'` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.

Relationship contracts: `FK_Payment_loan`: (Loan_ID) → `legacy_d_loan` (Loan_ID), via ledger.

### Homs → `legacy_h_payment`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL DEFAULT '0'` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.

Relationship contracts: `FK_Payment_loan`: (Loan_ID) → `legacy_h_loan` (Loan_ID), via ledger.

### Latakia → `legacy_l_payment`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.

Relationship contracts: `FK_Payment_loan`: (Loan_ID) → `legacy_l_loan` (Loan_ID), via ledger.

### AlWadi → `legacy_w_payment`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Pay_ID` | `int NOT NULL` |
| `Pay_Amount` | `float(24,0) NOT NULL DEFAULT '0'` |
| `Pay_DueDate` | `date DEFAULT NULL` |
| `Pay_IsPaid` | `char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Loan_ID` | `int DEFAULT NULL` |
| `Pay_RecievedOnDate` | `date DEFAULT NULL` |
| `Pay_Note` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Pay_ID`)`; `KEY `FK_Payment_loan` (`Loan_ID`)`.

Relationship contracts: `FK_Payment_loan`: (Loan_ID) → `legacy_w_loan` (Loan_ID), via ledger.

## `person`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_person`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.

Relationship contracts: `FK_person_hometype`: (P_HomeType_ID) → `legacy_a_assettype` (ID), via ledger; `FK_person_maritalstatus`: (P_MaritalStatus_ID) → `legacy_a_maritalstatus` (ID), via ledger; `FK_person_parish`: (P_Parish_ID) → `legacy_a_parish` (ID), via ledger; `FK_person_priest`: (P_Priest_ID) → `legacy_a_priest` (Priest_ID), via ledger; `FK_person_priest2`: (P_Priest2_ID) → `legacy_a_priest` (Priest_ID), via ledger; `FK_person_state`: (P_State_ID) → `legacy_a_personstate` (ID), via ledger; `FK_person_street`: (Street_ID) → `legacy_a_street` (ID), via ledger.

### Damascus → `legacy_d_person`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.

Relationship contracts: `FK_person_hometype`: (P_HomeType_ID) → `legacy_d_assettype` (ID), via ledger; `FK_person_maritalstatus`: (P_MaritalStatus_ID) → `legacy_d_maritalstatus` (ID), via ledger; `FK_person_parish`: (P_Parish_ID) → `legacy_d_parish` (ID), via ledger; `FK_person_priest`: (P_Priest_ID) → `legacy_d_priest` (Priest_ID), via ledger; `FK_person_priest2`: (P_Priest2_ID) → `legacy_d_priest` (Priest_ID), via ledger; `FK_person_state`: (P_State_ID) → `legacy_d_personstate` (ID), via ledger; `FK_person_street`: (Street_ID) → `legacy_d_street` (ID), via ledger.

### Homs → `legacy_h_person`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.

Relationship contracts: `FK_person_hometype`: (P_HomeType_ID) → `legacy_h_assettype` (ID), via ledger; `FK_person_maritalstatus`: (P_MaritalStatus_ID) → `legacy_h_maritalstatus` (ID), via ledger; `FK_person_parish`: (P_Parish_ID) → `legacy_h_parish` (ID), via ledger; `FK_person_priest`: (P_Priest_ID) → `legacy_h_priest` (Priest_ID), via ledger; `FK_person_priest2`: (P_Priest2_ID) → `legacy_h_priest` (Priest_ID), via ledger; `FK_person_state`: (P_State_ID) → `legacy_h_personstate` (ID), via ledger; `FK_person_street`: (Street_ID) → `legacy_h_street` (ID), via ledger.

### Latakia → `legacy_l_person`

Identity: typed PK tuple. Columns: 36. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.

Relationship contracts: `FK_person_hometype`: (P_HomeType_ID) → `legacy_l_assettype` (ID), via ledger; `FK_person_maritalstatus`: (P_MaritalStatus_ID) → `legacy_l_maritalstatus` (ID), via ledger; `FK_person_parish`: (P_Parish_ID) → `legacy_l_parish` (ID), via ledger; `FK_person_priest`: (P_Priest_ID) → `legacy_l_priest` (Priest_ID), via ledger; `FK_person_priest2`: (P_Priest2_ID) → `legacy_l_priest` (Priest_ID), via ledger; `FK_person_state`: (P_State_ID) → `legacy_l_personstate` (ID), via ledger; `FK_person_street`: (Street_ID) → `legacy_l_street` (ID), via ledger.

### AlWadi → `legacy_w_person`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`P_ID`)`; `KEY `FK_person_street` (`Street_ID`)`; `KEY `FK_person_priest` (`P_Priest_ID`)`; `KEY `FK_person_maritalstatus` (`P_MaritalStatus_ID`)`; `KEY `FK_person_priest2` (`P_Priest2_ID`)`; `KEY `FK_person_hometype` (`P_HomeType_ID`)`; `KEY `FK_person_parish` (`P_Parish_ID`)`; `KEY `FK_person_state` (`P_State_ID`)`.

Relationship contracts: `FK_person_hometype`: (P_HomeType_ID) → `legacy_w_assettype` (ID), via ledger; `FK_person_maritalstatus`: (P_MaritalStatus_ID) → `legacy_w_maritalstatus` (ID), via ledger; `FK_person_parish`: (P_Parish_ID) → `legacy_w_parish` (ID), via ledger; `FK_person_priest`: (P_Priest_ID) → `legacy_w_priest` (Priest_ID), via ledger; `FK_person_priest2`: (P_Priest2_ID) → `legacy_w_priest` (Priest_ID), via ledger; `FK_person_state`: (P_State_ID) → `legacy_w_personstate` (ID), via ledger; `FK_person_street`: (Street_ID) → `legacy_w_street` (ID), via ledger.

## `person_aidsource`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_aidsource`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `Person_AidSource1` (`AidSource_ID`)`; `KEY `Person_AidSource2` (`Person_ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.

Relationship contracts: `Person_AidSource1`: (AidSource_ID) → `legacy_a_aidsource` (ID), via ledger; `Person_AidSource2`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `Person_AidSource3`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_person_aidsource`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `Person_AidSource1` (`AidSource_ID`)`; `KEY `Person_AidSource2` (`Person_ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.

Relationship contracts: `Person_AidSource1`: (AidSource_ID) → `legacy_d_aidsource` (ID), via ledger; `Person_AidSource2`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `Person_AidSource3`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_person_aidsource`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `Person_AidSource1` (`AidSource_ID`)`; `KEY `Person_AidSource2` (`Person_ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.

Relationship contracts: `Person_AidSource1`: (AidSource_ID) → `legacy_h_aidsource` (ID), via ledger; `Person_AidSource2`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `Person_AidSource3`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_person_aidsource`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.

Relationship contracts: `Person_AidSource3`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_person_aidsource`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `DateYear` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Amount` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `ProjectType` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `Person_AidSource1` (`AidSource_ID`)`; `KEY `Person_AidSource2` (`Person_ID`)`; `KEY `Person_AidSource3` (`MicroProject_ID`)`.

Relationship contracts: `Person_AidSource1`: (AidSource_ID) → `legacy_w_aidsource` (ID), via ledger; `Person_AidSource2`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `Person_AidSource3`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `person_asset`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_asset`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.

Relationship contracts: `FK_Passets_assetbenefits`: (Asset_Benefits_ID) → `legacy_a_assetbenefits` (ID), via ledger; `FK_person_asset1`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `FK_person_asset2`: (Asset_ID) → `legacy_a_asset` (ID), via ledger; `FK_person_asset4`: (Asset_Type_ID) → `legacy_a_assettype` (ID), via ledger.

### Damascus → `legacy_d_person_asset`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.

Relationship contracts: `FK_Passets_assetbenefits`: (Asset_Benefits_ID) → `legacy_d_assetbenefits` (ID), via ledger; `FK_person_asset1`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `FK_person_asset2`: (Asset_ID) → `legacy_d_asset` (ID), via ledger; `FK_person_asset4`: (Asset_Type_ID) → `legacy_d_assettype` (ID), via ledger.

### Homs → `legacy_h_person_asset`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.

Relationship contracts: `FK_Passets_assetbenefits`: (Asset_Benefits_ID) → `legacy_h_assetbenefits` (ID), via ledger; `FK_person_asset1`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `FK_person_asset2`: (Asset_ID) → `legacy_h_asset` (ID), via ledger; `FK_person_asset4`: (Asset_Type_ID) → `legacy_h_assettype` (ID), via ledger.

### Latakia → `legacy_l_person_asset`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.

Relationship contracts: `FK_Passets_assetbenefits`: (Asset_Benefits_ID) → `legacy_l_assetbenefits` (ID), via ledger; `FK_person_asset1`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `FK_person_asset2`: (Asset_ID) → `legacy_l_asset` (ID), via ledger; `FK_person_asset4`: (Asset_Type_ID) → `legacy_l_assettype` (ID), via ledger.

### AlWadi → `legacy_w_person_asset`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Asset_ID` | `int DEFAULT NULL` |
| `Asset_Type_ID` | `int DEFAULT NULL` |
| `Asset_Benefits_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_asset1` (`Person_ID`)`; `KEY `FK_person_asset2` (`Asset_ID`)`; `KEY `FK_person_asset4` (`Asset_Type_ID`)`; `KEY `FK_Passets_assetbenefits` (`Asset_Benefits_ID`)`.

Relationship contracts: `FK_Passets_assetbenefits`: (Asset_Benefits_ID) → `legacy_w_assetbenefits` (ID), via ledger; `FK_person_asset1`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `FK_person_asset2`: (Asset_ID) → `legacy_w_asset` (ID), via ledger; `FK_person_asset4`: (Asset_Type_ID) → `legacy_w_assettype` (ID), via ledger.

## `person_checklistmembers`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_checklistmembers`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.

Relationship contracts: `FK_Member_checklist`: (Member_ID) → `legacy_a_checklist_member` (ID), via ledger; `FK_Person_checklist`: (Person_ID) → `legacy_a_person` (P_ID), via ledger.

### Damascus → `legacy_d_person_checklistmembers`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.

Relationship contracts: `FK_Member_checklist`: (Member_ID) → `legacy_d_checklist_member` (ID), via ledger; `FK_Person_checklist`: (Person_ID) → `legacy_d_person` (P_ID), via ledger.

### Homs → `legacy_h_person_checklistmembers`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.

Relationship contracts: `FK_Member_checklist`: (Member_ID) → `legacy_h_checklist_member` (ID), via ledger; `FK_Person_checklist`: (Person_ID) → `legacy_h_person` (P_ID), via ledger.

### Latakia → `legacy_l_person_checklistmembers`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.

Relationship contracts: `FK_Member_checklist`: (Member_ID) → `legacy_l_checklist_member` (ID), via ledger; `FK_Person_checklist`: (Person_ID) → `legacy_l_person` (P_ID), via ledger.

### AlWadi → `legacy_w_person_checklistmembers`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Member_checklist` (`Member_ID`)`; `KEY `FK_Person_checklist` (`Person_ID`)`.

Relationship contracts: `FK_Member_checklist`: (Member_ID) → `legacy_w_checklist_member` (ID), via ledger; `FK_Person_checklist`: (Person_ID) → `legacy_w_person` (P_ID), via ledger.

## `person_course`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_course`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.

Relationship contracts: `fk_person_course2`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `fk_person_course3`: (SubCategory_ID) → `legacy_a_subcategory` (ID), via ledger.

### Damascus → `legacy_d_person_course`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.

Relationship contracts: `fk_person_course2`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `fk_person_course3`: (SubCategory_ID) → `legacy_d_subcategory` (ID), via ledger.

### Homs → `legacy_h_person_course`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.

Relationship contracts: `fk_person_course2`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `fk_person_course3`: (SubCategory_ID) → `legacy_h_subcategory` (ID), via ledger.

### Latakia → `legacy_l_person_course`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.

Relationship contracts: `fk_person_course2`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `fk_person_course3`: (SubCategory_ID) → `legacy_l_subcategory` (ID), via ledger.

### AlWadi → `legacy_w_person_course`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Place` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Donor` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Date` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `SubCategory_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `fk_person_course2` (`Person_ID`)`; `KEY `fk_person_course3` (`SubCategory_ID`)`.

Relationship contracts: `fk_person_course2`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `fk_person_course3`: (SubCategory_ID) → `legacy_w_subcategory` (ID), via ledger.

## `person_education`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.

Relationship contracts: `FK_Person_Education3`: (Level_ID) → `legacy_a_educationlevel` (ID), via ledger; `FK_Person_Education_Education`: (Education_ID) → `legacy_a_education` (ID), via ledger; `FK_Person_Education_Person`: (Person_ID) → `legacy_a_person` (P_ID), via ledger.

### Damascus → `legacy_d_person_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.

Relationship contracts: `FK_Person_Education3`: (Level_ID) → `legacy_d_educationlevel` (ID), via ledger; `FK_Person_Education_Education`: (Education_ID) → `legacy_d_education` (ID), via ledger; `FK_Person_Education_Person`: (Person_ID) → `legacy_d_person` (P_ID), via ledger.

### Homs → `legacy_h_person_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.

Relationship contracts: `FK_Person_Education3`: (Level_ID) → `legacy_h_educationlevel` (ID), via ledger; `FK_Person_Education_Education`: (Education_ID) → `legacy_h_education` (ID), via ledger; `FK_Person_Education_Person`: (Person_ID) → `legacy_h_person` (P_ID), via ledger.

### Latakia → `legacy_l_person_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.

Relationship contracts: `FK_Person_Education3`: (Level_ID) → `legacy_l_educationlevel` (ID), via ledger; `FK_Person_Education_Education`: (Education_ID) → `legacy_l_education` (ID), via ledger; `FK_Person_Education_Person`: (Person_ID) → `legacy_l_person` (P_ID), via ledger.

### AlWadi → `legacy_w_person_education`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Education_ID` | `int DEFAULT NULL` |
| `Level_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Education_Education` (`Education_ID`)`; `KEY `FK_Person_Education_Person` (`Person_ID`)`; `KEY `FK_Person_Education3` (`Level_ID`)`.

Relationship contracts: `FK_Person_Education3`: (Level_ID) → `legacy_w_educationlevel` (ID), via ledger; `FK_Person_Education_Education`: (Education_ID) → `legacy_w_education` (ID), via ledger; `FK_Person_Education_Person`: (Person_ID) → `legacy_w_person` (P_ID), via ledger.

## `person_family`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_family`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.

Relationship contracts: `FK_Person_Education`: (Education_ID) → `legacy_a_education` (ID), via ledger; `FK_Person_educationlevel`: (EducationLevel_ID) → `legacy_a_educationlevel` (ID), via ledger; `FK_Person_Family1`: (Relation_ID) → `legacy_a_relation` (ID), via ledger; `FK_Person_Family_Family`: (Family_ID) → `legacy_a_family` (F_ID), via ledger; `FK_Person_Family_Person`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `FK_Person_Family_Provider`: (P_Provider_ID) → `legacy_a_person` (P_ID), via ledger.

### Damascus → `legacy_d_person_family`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.

Relationship contracts: `FK_Person_Education`: (Education_ID) → `legacy_d_education` (ID), via ledger; `FK_Person_educationlevel`: (EducationLevel_ID) → `legacy_d_educationlevel` (ID), via ledger; `FK_Person_Family1`: (Relation_ID) → `legacy_d_relation` (ID), via ledger; `FK_Person_Family_Family`: (Family_ID) → `legacy_d_family` (F_ID), via ledger; `FK_Person_Family_Person`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `FK_Person_Family_Provider`: (P_Provider_ID) → `legacy_d_person` (P_ID), via ledger.

### Homs → `legacy_h_person_family`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.

Relationship contracts: `FK_Person_Education`: (Education_ID) → `legacy_h_education` (ID), via ledger; `FK_Person_educationlevel`: (EducationLevel_ID) → `legacy_h_educationlevel` (ID), via ledger; `FK_Person_Family1`: (Relation_ID) → `legacy_h_relation` (ID), via ledger; `FK_Person_Family_Family`: (Family_ID) → `legacy_h_family` (F_ID), via ledger; `FK_Person_Family_Person`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `FK_Person_Family_Provider`: (P_Provider_ID) → `legacy_h_person` (P_ID), via ledger.

### Latakia → `legacy_l_person_family`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.

Relationship contracts: `FK_Person_Education`: (Education_ID) → `legacy_l_education` (ID), via ledger; `FK_Person_educationlevel`: (EducationLevel_ID) → `legacy_l_educationlevel` (ID), via ledger; `FK_Person_Family1`: (Relation_ID) → `legacy_l_relation` (ID), via ledger; `FK_Person_Family_Family`: (Family_ID) → `legacy_l_family` (F_ID), via ledger; `FK_Person_Family_Person`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `FK_Person_Family_Provider`: (P_Provider_ID) → `legacy_l_person` (P_ID), via ledger.

### AlWadi → `legacy_w_person_family`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PF_ID`)`; `KEY `FK_Person_Family_Family` (`Family_ID`)`; `KEY `FK_Person_Family_Person` (`Person_ID`)`; `KEY `FK_Person_Family_Provider` (`P_Provider_ID`)`; `KEY `FK_Person_Family1` (`Relation_ID`)`; `KEY `FK_Person_educationlevel` (`EducationLevel_ID`)`; `KEY `FK_Person_Education` (`Education_ID`)`.

Relationship contracts: `FK_Person_Education`: (Education_ID) → `legacy_w_education` (ID), via ledger; `FK_Person_educationlevel`: (EducationLevel_ID) → `legacy_w_educationlevel` (ID), via ledger; `FK_Person_Family1`: (Relation_ID) → `legacy_w_relation` (ID), via ledger; `FK_Person_Family_Family`: (Family_ID) → `legacy_w_family` (F_ID), via ledger; `FK_Person_Family_Person`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `FK_Person_Family_Provider`: (P_Provider_ID) → `legacy_w_person` (P_ID), via ledger.

## `person_goals`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_goals`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.

Relationship contracts: `FK_goals1`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `FK_goals2`: (Goal_ID) → `legacy_a_goal` (ID), via ledger.

### Damascus → `legacy_d_person_goals`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.

Relationship contracts: `FK_goals1`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `FK_goals2`: (Goal_ID) → `legacy_d_goal` (ID), via ledger.

### Homs → `legacy_h_person_goals`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.

Relationship contracts: `FK_goals1`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `FK_goals2`: (Goal_ID) → `legacy_h_goal` (ID), via ledger.

### Latakia → `legacy_l_person_goals`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.

Relationship contracts: `FK_goals1`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `FK_goals2`: (Goal_ID) → `legacy_l_goal` (ID), via ledger.

### AlWadi → `legacy_w_person_goals`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Goal_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_goals1` (`Person_ID`)`; `KEY `FK_goals2` (`Goal_ID`)`.

Relationship contracts: `FK_goals1`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `FK_goals2`: (Goal_ID) → `legacy_w_goal` (ID), via ledger.

## `person_help`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_help`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.

Relationship contracts: `person_help1`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `person_help2`: (AidSource_ID) → `legacy_a_aidsource` (ID), via ledger; `person_help3`: (Aid_ID) → `legacy_a_aid` (ID), via ledger; `person_help4`: (Aidperiod_ID) → `legacy_a_aidperiod` (ID), via ledger.

### Damascus → `legacy_d_person_help`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.

Relationship contracts: `person_help1`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `person_help2`: (AidSource_ID) → `legacy_d_aidsource` (ID), via ledger; `person_help3`: (Aid_ID) → `legacy_d_aid` (ID), via ledger; `person_help4`: (Aidperiod_ID) → `legacy_d_aidperiod` (ID), via ledger.

### Homs → `legacy_h_person_help`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.

Relationship contracts: `person_help1`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `person_help2`: (AidSource_ID) → `legacy_h_aidsource` (ID), via ledger; `person_help3`: (Aid_ID) → `legacy_h_aid` (ID), via ledger; `person_help4`: (Aidperiod_ID) → `legacy_h_aidperiod` (ID), via ledger.

### Latakia → `legacy_l_person_help`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.

Relationship contracts: `person_help1`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `person_help2`: (AidSource_ID) → `legacy_l_aidsource` (ID), via ledger; `person_help3`: (Aid_ID) → `legacy_l_aid` (ID), via ledger; `person_help4`: (Aidperiod_ID) → `legacy_l_aidperiod` (ID), via ledger.

### AlWadi → `legacy_w_person_help`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `AidSource_ID` | `int DEFAULT NULL` |
| `Aid_ID` | `int DEFAULT NULL` |
| `Aidperiod_ID` | `int DEFAULT NULL` |
| `Notes` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `person_help1` (`Person_ID`)`; `KEY `person_help2` (`AidSource_ID`)`; `KEY `person_help3` (`Aid_ID`)`; `KEY `person_help4` (`Aidperiod_ID`)`.

Relationship contracts: `person_help1`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `person_help2`: (AidSource_ID) → `legacy_w_aidsource` (ID), via ledger; `person_help3`: (Aid_ID) → `legacy_w_aid` (ID), via ledger; `person_help4`: (Aidperiod_ID) → `legacy_w_aidperiod` (ID), via ledger.

## `person_illness`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_illness`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.

Relationship contracts: `FK_person_illness1`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `FK_person_illness2`: (Illness_ID) → `legacy_a_illness` (ID), via ledger; `FK_person_illness4`: (AidPeriod_ID) → `legacy_a_aidperiod` (ID), via ledger; `FK_person_illness5`: (MedicalNeeds_ID) → `legacy_a_medicalneeds` (ID), via ledger; `FK_person_illness_aidsource`: (AidSource_ID) → `legacy_a_aidsource` (ID), via ledger.

### Damascus → `legacy_d_person_illness`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.

Relationship contracts: `FK_person_illness1`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `FK_person_illness2`: (Illness_ID) → `legacy_d_illness` (ID), via ledger; `FK_person_illness4`: (AidPeriod_ID) → `legacy_d_aidperiod` (ID), via ledger; `FK_person_illness5`: (MedicalNeeds_ID) → `legacy_d_medicalneeds` (ID), via ledger; `FK_person_illness_aidsource`: (AidSource_ID) → `legacy_d_aidsource` (ID), via ledger.

### Homs → `legacy_h_person_illness`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.

Relationship contracts: `FK_person_illness1`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `FK_person_illness2`: (Illness_ID) → `legacy_h_illness` (ID), via ledger; `FK_person_illness4`: (AidPeriod_ID) → `legacy_h_aidperiod` (ID), via ledger; `FK_person_illness5`: (MedicalNeeds_ID) → `legacy_h_medicalneeds` (ID), via ledger; `FK_person_illness_aidsource`: (AidSource_ID) → `legacy_h_aidsource` (ID), via ledger.

### Latakia → `legacy_l_person_illness`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.

Relationship contracts: `FK_person_illness1`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `FK_person_illness2`: (Illness_ID) → `legacy_l_illness` (ID), via ledger; `FK_person_illness4`: (AidPeriod_ID) → `legacy_l_aidperiod` (ID), via ledger; `FK_person_illness5`: (MedicalNeeds_ID) → `legacy_l_medicalneeds` (ID), via ledger; `FK_person_illness_aidsource`: (AidSource_ID) → `legacy_l_aidsource` (ID), via ledger.

### AlWadi → `legacy_w_person_illness`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_illness1` (`Person_ID`)`; `KEY `FK_person_illness2` (`Illness_ID`)`; `KEY `FK_person_illness4` (`AidPeriod_ID`)`; `KEY `FK_person_illness5` (`MedicalNeeds_ID`)`; `KEY `FK_person_illness_aidsource` (`AidSource_ID`)`.

Relationship contracts: `FK_person_illness1`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `FK_person_illness2`: (Illness_ID) → `legacy_w_illness` (ID), via ledger; `FK_person_illness4`: (AidPeriod_ID) → `legacy_w_aidperiod` (ID), via ledger; `FK_person_illness5`: (MedicalNeeds_ID) → `legacy_w_medicalneeds` (ID), via ledger; `FK_person_illness_aidsource`: (AidSource_ID) → `legacy_w_aidsource` (ID), via ledger.

## `person_loss`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_loss`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.

Relationship contracts: `FK_Loss_Person_`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `FK_Person_Loss`: (Loss_ID) → `legacy_a_loss` (ID), via ledger.

### Damascus → `legacy_d_person_loss`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.

Relationship contracts: `FK_Person_Loss`: (Loss_ID) → `legacy_d_loss` (ID), via ledger; `FK_Person_Loss1`: (Person_ID) → `legacy_d_person` (P_ID), via ledger.

### Homs → `legacy_h_person_loss`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.

Relationship contracts: `FK_Person_Loss`: (Loss_ID) → `legacy_h_loss` (ID), via ledger; `FK_Person_Loss1`: (Person_ID) → `legacy_h_person` (P_ID), via ledger.

### Latakia → `legacy_l_person_loss`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.

Relationship contracts: `FK_Loss_Person_`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `FK_Person_Loss`: (Loss_ID) → `legacy_l_loss` (ID), via ledger.

### AlWadi → `legacy_w_person_loss`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `Loss_ID` | `int DEFAULT NULL` |
| `Year` | `varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Note` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_Loss` (`Loss_ID`)`; `KEY `FK_Loss_Person_` (`Person_ID`)`.

Relationship contracts: `FK_Person_Loss`: (Loss_ID) → `legacy_w_loss` (ID), via ledger; `FK_Person_Loss1`: (Person_ID) → `legacy_w_person` (P_ID), via ledger.

## `person_microproject`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_microproject`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Person_MicroProject_MicroProject`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_Person_MicroProject_Person`: (Person_ID) → `legacy_a_person` (P_ID), via ledger.

### Damascus → `legacy_d_person_microproject`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Person_MicroProject_MicroProject`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_Person_MicroProject_Person`: (Person_ID) → `legacy_d_person` (P_ID), via ledger.

### Homs → `legacy_h_person_microproject`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Person_MicroProject_MicroProject`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_Person_MicroProject_Person`: (Person_ID) → `legacy_h_person` (P_ID), via ledger.

### Latakia → `legacy_l_person_microproject`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Person_MicroProject_MicroProject`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_Person_MicroProject_Person`: (Person_ID) → `legacy_l_person` (P_ID), via ledger.

### AlWadi → `legacy_w_person_microproject`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`PMP_ID`)`; `KEY `FK_Person_MicroProject_Person` (`Person_ID`)`; `KEY `FK_Person_MicroProject_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Person_MicroProject_MicroProject`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_Person_MicroProject_Person`: (Person_ID) → `legacy_w_person` (P_ID), via ledger.

## `person_powersource`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_person_power1`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `FK_person_power2`: (PowerSource_ID) → `legacy_a_powersource` (ID), via ledger.

### Damascus → `legacy_d_person_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_person_power1`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `FK_person_power2`: (PowerSource_ID) → `legacy_d_powersource` (ID), via ledger.

### Homs → `legacy_h_person_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_person_power1`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `FK_person_power2`: (PowerSource_ID) → `legacy_h_powersource` (ID), via ledger.

### Latakia → `legacy_l_person_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_person_power1`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `FK_person_power2`: (PowerSource_ID) → `legacy_l_powersource` (ID), via ledger.

### AlWadi → `legacy_w_person_powersource`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `PowerSource_ID` | `int DEFAULT NULL` |
| `Note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Cost` | `double NOT NULL DEFAULT '0'` |
| `Responsability` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_person_power1` (`Person_ID`)`; `KEY `FK_person_power2` (`PowerSource_ID`)`.

Relationship contracts: `FK_person_power1`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `FK_person_power2`: (PowerSource_ID) → `legacy_w_powersource` (ID), via ledger.

## `person_workexp`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_person_workexp`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.

Relationship contracts: `FK_Person_WorkExp1`: (W_WorkType_ID) → `legacy_a_worktype` (ID), via ledger; `FK_Person_WorkExp_MicroProject`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_Person_WorkExp_Person`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `FK_Person_WorkExp_Work`: (Work_ID) → `legacy_a_work` (W_ID), via ledger.

### Damascus → `legacy_d_person_workexp`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.

Relationship contracts: `FK_Person_WorkExp1`: (W_WorkType_ID) → `legacy_d_worktype` (ID), via ledger; `FK_Person_WorkExp_MicroProject`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_Person_WorkExp_Person`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `FK_Person_WorkExp_Work`: (Work_ID) → `legacy_d_work` (W_ID), via ledger.

### Homs → `legacy_h_person_workexp`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.

Relationship contracts: `FK_Person_WorkExp1`: (W_WorkType_ID) → `legacy_h_worktype` (ID), via ledger; `FK_Person_WorkExp_MicroProject`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_Person_WorkExp_Person`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `FK_Person_WorkExp_Work`: (Work_ID) → `legacy_h_work` (W_ID), via ledger.

### Latakia → `legacy_l_person_workexp`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.

Relationship contracts: `FK_Person_WorkExp1`: (W_WorkType_ID) → `legacy_l_worktype` (ID), via ledger; `FK_Person_WorkExp_MicroProject`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_Person_WorkExp_Person`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `FK_Person_WorkExp_Work`: (Work_ID) → `legacy_l_work` (W_ID), via ledger.

### AlWadi → `legacy_w_person_workexp`

Identity: typed PK tuple. Columns: 14. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Person_WorkExp_Work` (`Work_ID`)`; `KEY `FK_Person_WorkExp_Person` (`Person_ID`)`; `KEY `FK_Person_WorkExp_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Person_WorkExp1` (`W_WorkType_ID`)`.

Relationship contracts: `FK_Person_WorkExp1`: (W_WorkType_ID) → `legacy_w_worktype` (ID), via ledger; `FK_Person_WorkExp_MicroProject`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_Person_WorkExp_Person`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `FK_Person_WorkExp_Work`: (Work_ID) → `legacy_w_work` (W_ID), via ledger.

## `personstate`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_personstate`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_personstate`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_personstate`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_personstate`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_personstate`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `Name` (`Name`)`.

Relationship contracts: No declared source foreign keys..

## `powersource`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_powersource`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_powersource`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_powersource`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_powersource`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_powersource`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `priest`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_priest`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Priest_ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_priest`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Priest_ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_priest`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Priest_ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_priest`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Priest_ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_priest`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Priest_ID` | `int NOT NULL` |
| `Priest_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Priest_ID`)`.

Relationship contracts: No declared source foreign keys..

## `profession_meeting`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting`

Identity: typed PK tuple. Columns: 13. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.

Relationship contracts: `fk_profession_meeting_career`: (Career_ID) → `legacy_a_career` (ID), via ledger; `fk_profession_meeting_edited_user`: (Edited_By_UserID) → `legacy_a_user` (UserID), via ledger; `fk_profession_meeting_paper_user`: (PaperRegistrar_UserID) → `legacy_a_user` (UserID), via ledger; `fk_profession_meeting_program_user`: (ProgramRegistrar_UserID) → `legacy_a_user` (UserID), via ledger; `fk_profession_meeting_subcategory`: (SubCategory_ID) → `legacy_a_subcategory` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting`

Identity: typed PK tuple. Columns: 13. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.

Relationship contracts: `fk_profession_meeting_career`: (Career_ID) → `legacy_d_career` (ID), via ledger; `fk_profession_meeting_edited_user`: (Edited_By_UserID) → `legacy_d_user` (UserID), via ledger; `fk_profession_meeting_paper_user`: (PaperRegistrar_UserID) → `legacy_d_user` (UserID), via ledger; `fk_profession_meeting_program_user`: (ProgramRegistrar_UserID) → `legacy_d_user` (UserID), via ledger; `fk_profession_meeting_subcategory`: (SubCategory_ID) → `legacy_d_subcategory` (ID), via ledger.

### Homs → `legacy_h_profession_meeting`

Identity: typed PK tuple. Columns: 13. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.

Relationship contracts: `fk_profession_meeting_career`: (Career_ID) → `legacy_h_career` (ID), via ledger; `fk_profession_meeting_edited_user`: (Edited_By_UserID) → `legacy_h_user` (UserID), via ledger; `fk_profession_meeting_paper_user`: (PaperRegistrar_UserID) → `legacy_h_user` (UserID), via ledger; `fk_profession_meeting_program_user`: (ProgramRegistrar_UserID) → `legacy_h_user` (UserID), via ledger; `fk_profession_meeting_subcategory`: (SubCategory_ID) → `legacy_h_subcategory` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting`

Identity: typed PK tuple. Columns: 13. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.

Relationship contracts: `fk_profession_meeting_career`: (Career_ID) → `legacy_l_career` (ID), via ledger; `fk_profession_meeting_edited_user`: (Edited_By_UserID) → `legacy_l_user` (UserID), via ledger; `fk_profession_meeting_paper_user`: (PaperRegistrar_UserID) → `legacy_l_user` (UserID), via ledger; `fk_profession_meeting_program_user`: (ProgramRegistrar_UserID) → `legacy_l_user` (UserID), via ledger; `fk_profession_meeting_subcategory`: (SubCategory_ID) → `legacy_l_subcategory` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting`

Identity: typed PK tuple. Columns: 13. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_meeting_career` (`Career_ID`)`; `KEY `idx_profession_meeting_subcategory` (`SubCategory_ID`)`; `KEY `idx_profession_meeting_date` (`MeetingDate`)`; `KEY `idx_profession_meeting_paper_user` (`PaperRegistrar_UserID`)`; `KEY `idx_profession_meeting_program_user` (`ProgramRegistrar_UserID`)`; `KEY `idx_profession_meeting_edited_user` (`Edited_By_UserID`)`.

Relationship contracts: `fk_profession_meeting_career`: (Career_ID) → `legacy_w_career` (ID), via ledger; `fk_profession_meeting_edited_user`: (Edited_By_UserID) → `legacy_w_user` (UserID), via ledger; `fk_profession_meeting_paper_user`: (PaperRegistrar_UserID) → `legacy_w_user` (UserID), via ledger; `fk_profession_meeting_program_user`: (ProgramRegistrar_UserID) → `legacy_w_user` (UserID), via ledger; `fk_profession_meeting_subcategory`: (SubCategory_ID) → `legacy_w_subcategory` (ID), via ledger.

## `profession_meeting_answer`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_meeting_answer_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger; `fk_profession_meeting_answer_question`: (Question_ID) → `legacy_a_profession_question` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_meeting_answer_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger; `fk_profession_meeting_answer_question`: (Question_ID) → `legacy_d_profession_question` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_meeting_answer_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger; `fk_profession_meeting_answer_question`: (Question_ID) → `legacy_h_profession_question` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_meeting_answer_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger; `fk_profession_meeting_answer_question`: (Question_ID) → `legacy_l_profession_question` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_answer`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value_Text` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Value_Number` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_answer_question` (`Meeting_ID`,`Question_ID`)`; `KEY `idx_profession_meeting_answer_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_meeting_answer_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger; `fk_profession_meeting_answer_question`: (Question_ID) → `legacy_w_profession_question` (ID), via ledger.

## `profession_meeting_attendee`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_attendee`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_attendee_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger; `fk_profession_attendee_person`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `fk_profession_attendee_project`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_profession_meeting_attendee`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_attendee_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger; `fk_profession_attendee_person`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `fk_profession_attendee_project`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_profession_meeting_attendee`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_attendee_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger; `fk_profession_attendee_person`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `fk_profession_attendee_project`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_profession_meeting_attendee`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_attendee_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger; `fk_profession_attendee_person`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `fk_profession_attendee_project`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_attendee`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Attendee_Name` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Speciality` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_attendee_meeting` (`Meeting_ID`)`; `KEY `idx_profession_attendee_person` (`Person_ID`)`; `KEY `idx_profession_attendee_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_attendee_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger; `fk_profession_attendee_person`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `fk_profession_attendee_project`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `profession_meeting_brand`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_brand`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_brand_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_brand`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_brand_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_brand`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_brand_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_brand`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_brand_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_brand`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Brand_Name` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_brand_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_brand_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger.

## `profession_meeting_challenge`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_challenge`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.

Relationship contracts: `fk_profession_challenge_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_challenge`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.

Relationship contracts: `fk_profession_challenge_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_challenge`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.

Relationship contracts: `fk_profession_challenge_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_challenge`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.

Relationship contracts: `fk_profession_challenge_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_challenge`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Challenge` | `varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Challenge_Count` | `int DEFAULT NULL` |
| `Suggested_Solution` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_challenge_meeting` (`Meeting_ID`)`; `KEY `idx_profession_challenge_text` (`Challenge`(100))`.

Relationship contracts: `fk_profession_challenge_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger.

## `profession_meeting_competition`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_competition`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.

Relationship contracts: `fk_profession_competition_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_competition`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.

Relationship contracts: `fk_profession_competition_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_competition`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.

Relationship contracts: `fk_profession_competition_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_competition`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.

Relationship contracts: `fk_profession_competition_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_competition`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Competition_Level` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `CountValue` | `int DEFAULT NULL` |
| `Reason` | `varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_competition_level` (`Meeting_ID`,`Competition_Level`)`.

Relationship contracts: `fk_profession_competition_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger.

## `profession_meeting_equipment`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_equipment`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_equipment_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_equipment`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_equipment_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_equipment`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_equipment_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_equipment`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_equipment_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_equipment`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Specialization` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Equipment` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_equipment_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_equipment_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger.

## `profession_meeting_minute`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_minute`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text COLLATE utf8mb3_unicode_ci` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_minute_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger; `fk_profession_minute_section`: (Section_ID) → `legacy_a_profession_section` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_minute`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_minute_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger; `fk_profession_minute_section`: (Section_ID) → `legacy_d_profession_section` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_minute`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_minute_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger; `fk_profession_minute_section`: (Section_ID) → `legacy_h_profession_section` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_minute`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text COLLATE utf8mb3_unicode_ci` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_minute_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger; `fk_profession_minute_section`: (Section_ID) → `legacy_l_profession_section` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_minute`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Notes` | `text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_minute_section` (`Meeting_ID`,`Section_ID`)`; `KEY `idx_profession_minute_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_minute_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger; `fk_profession_minute_section`: (Section_ID) → `legacy_w_profession_section` (ID), via ledger.

## `profession_meeting_option_answer`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_option_answer`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.

Relationship contracts: `fk_profession_option_answer_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger; `fk_profession_option_answer_option`: (Option_ID) → `legacy_a_profession_option` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_option_answer`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.

Relationship contracts: `fk_profession_option_answer_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger; `fk_profession_option_answer_option`: (Option_ID) → `legacy_d_profession_option` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_option_answer`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.

Relationship contracts: `fk_profession_option_answer_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger; `fk_profession_option_answer_option`: (Option_ID) → `legacy_h_profession_option` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_option_answer`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.

Relationship contracts: `fk_profession_option_answer_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger; `fk_profession_option_answer_option`: (Option_ID) → `legacy_l_profession_option` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_option_answer`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Option_ID` | `int NOT NULL` |
| `Is_Selected` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Rank_Value` | `int DEFAULT NULL` |
| `Other_Text` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_meeting_option_answer` (`Meeting_ID`,`Option_ID`)`; `KEY `idx_profession_option_answer_option` (`Option_ID`)`.

Relationship contracts: `fk_profession_option_answer_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger; `fk_profession_option_answer_option`: (Option_ID) → `legacy_w_profession_option` (ID), via ledger.

## `profession_meeting_participant`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_participant`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_participant_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger; `fk_profession_participant_person`: (Person_ID) → `legacy_a_person` (P_ID), via ledger; `fk_profession_participant_project`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `fk_profession_participant_question`: (Question_ID) → `legacy_a_profession_question` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_participant`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_participant_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger; `fk_profession_participant_person`: (Person_ID) → `legacy_d_person` (P_ID), via ledger; `fk_profession_participant_project`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `fk_profession_participant_question`: (Question_ID) → `legacy_d_profession_question` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_participant`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_participant_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger; `fk_profession_participant_person`: (Person_ID) → `legacy_h_person` (P_ID), via ledger; `fk_profession_participant_project`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `fk_profession_participant_question`: (Question_ID) → `legacy_h_profession_question` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_participant`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_participant_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger; `fk_profession_participant_person`: (Person_ID) → `legacy_l_person` (P_ID), via ledger; `fk_profession_participant_project`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `fk_profession_participant_question`: (Question_ID) → `legacy_l_profession_question` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_participant`

Identity: typed PK tuple. Columns: 9. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_participant_meeting` (`Meeting_ID`)`; `KEY `idx_profession_participant_question` (`Question_ID`)`; `KEY `idx_profession_participant_person` (`Person_ID`)`; `KEY `idx_profession_participant_project` (`MicroProject_ID`)`.

Relationship contracts: `fk_profession_participant_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger; `fk_profession_participant_person`: (Person_ID) → `legacy_w_person` (P_ID), via ledger; `fk_profession_participant_project`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `fk_profession_participant_question`: (Question_ID) → `legacy_w_profession_question` (ID), via ledger.

## `profession_meeting_season`

Profession meeting workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_meeting_season`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_season_meeting`: (Meeting_ID) → `legacy_a_profession_meeting` (ID), via ledger.

### Damascus → `legacy_d_profession_meeting_season`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_season_meeting`: (Meeting_ID) → `legacy_d_profession_meeting` (ID), via ledger.

### Homs → `legacy_h_profession_meeting_season`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_season_meeting`: (Meeting_ID) → `legacy_h_profession_meeting` (ID), via ledger.

### Latakia → `legacy_l_profession_meeting_season`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_season_meeting`: (Meeting_ID) → `legacy_l_profession_meeting` (ID), via ledger.

### AlWadi → `legacy_w_profession_meeting_season`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Meeting_ID` | `int NOT NULL` |
| `Season_Count` | `int DEFAULT NULL` |
| `From_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `To_Period` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Reason` | `varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL` |
| `Sort_Order` | `smallint NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `idx_profession_season_meeting` (`Meeting_ID`)`.

Relationship contracts: `fk_profession_season_meeting`: (Meeting_ID) → `legacy_w_profession_meeting` (ID), via ledger.

## `profession_option`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_option`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_option_question`: (Question_ID) → `legacy_a_profession_question` (ID), via ledger.

### Damascus → `legacy_d_profession_option`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_option_question`: (Question_ID) → `legacy_d_profession_question` (ID), via ledger.

### Homs → `legacy_h_profession_option`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_option_question`: (Question_ID) → `legacy_h_profession_question` (ID), via ledger.

### Latakia → `legacy_l_profession_option`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_option_question`: (Question_ID) → `legacy_l_profession_question` (ID), via ledger.

### AlWadi → `legacy_w_profession_option`

Identity: typed PK tuple. Columns: 6. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Text_Ar` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Other` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Has_Details` | `tinyint(1) NOT NULL DEFAULT '0'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_option_question_text` (`Question_ID`,`Text_Ar`)`; `UNIQUE KEY `uq_profession_option_question_sort` (`Question_ID`,`Sort_Order`)`; `KEY `idx_profession_option_question` (`Question_ID`)`.

Relationship contracts: `fk_profession_option_question`: (Question_ID) → `legacy_w_profession_question` (ID), via ledger.

## `profession_question`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_question`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_question_section`: (Section_ID) → `legacy_a_profession_section` (ID), via ledger.

### Damascus → `legacy_d_profession_question`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_question_section`: (Section_ID) → `legacy_d_profession_section` (ID), via ledger.

### Homs → `legacy_h_profession_question`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_question_section`: (Section_ID) → `legacy_h_profession_section` (ID), via ledger.

### Latakia → `legacy_l_profession_question`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_question_section`: (Section_ID) → `legacy_l_profession_section` (ID), via ledger.

### AlWadi → `legacy_w_profession_question`

Identity: typed PK tuple. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Section_ID` | `tinyint NOT NULL` |
| `Number` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Text_Ar` | `varchar(700) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Answer_Type` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Is_Active` | `tinyint(1) NOT NULL DEFAULT '1'` |
| `Sort_Order` | `smallint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_question_number` (`Number`)`; `UNIQUE KEY `uq_profession_question_sort` (`Sort_Order`)`; `KEY `idx_profession_question_section` (`Section_ID`)`.

Relationship contracts: `fk_profession_question_section`: (Section_ID) → `legacy_w_profession_section` (ID), via ledger.

## `profession_section`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_profession_section`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_profession_section`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_profession_section`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_profession_section`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_profession_section`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `tinyint NOT NULL` |
| `Name_Ar` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL` |
| `Sort_Order` | `tinyint NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `UNIQUE KEY `uq_profession_section_sort` (`Sort_Order`)`.

Relationship contracts: No declared source foreign keys..

## `purchasing`

Finance/execution transaction; disposition: retain every source column and row.

### Aleppo → `legacy_a_purchasing`

Identity: typed PK tuple. Columns: 11. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.

Relationship contracts: `FK_Purchasing1`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_Purchasing2`: (ItemCategory_ID) → `legacy_a_purchasingcategory` (ID), via ledger.

### Damascus → `legacy_d_purchasing`

Identity: typed PK tuple. Columns: 11. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.

Relationship contracts: `FK_Purchasing1`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_Purchasing2`: (ItemCategory_ID) → `legacy_d_purchasingcategory` (ID), via ledger.

### Homs → `legacy_h_purchasing`

Identity: typed PK tuple. Columns: 11. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.

Relationship contracts: `FK_Purchasing1`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_Purchasing2`: (ItemCategory_ID) → `legacy_h_purchasingcategory` (ID), via ledger.

### Latakia → `legacy_l_purchasing`

Identity: typed PK tuple. Columns: 11. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.

Relationship contracts: `FK_Purchasing1`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_Purchasing2`: (ItemCategory_ID) → `legacy_l_purchasingcategory` (ID), via ledger.

### AlWadi → `legacy_w_purchasing`

Identity: typed PK tuple. Columns: 11. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Purchasing1` (`MicroProject_ID`)`; `KEY `FK_Purchasing2` (`ItemCategory_ID`)`.

Relationship contracts: `FK_Purchasing1`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_Purchasing2`: (ItemCategory_ID) → `legacy_w_purchasingcategory` (ID), via ledger.

## `purchasingcategory`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_purchasingcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_purchasingcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_purchasingcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_purchasingcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_purchasingcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `rehabanswer`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_rehabanswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.

Relationship contracts: `FK_RehabAnswer_Question`: (Question_ID) → `legacy_a_rehabquestion` (ID), via ledger.

### Damascus → `legacy_d_rehabanswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.

Relationship contracts: `FK_RehabAnswer_Question`: (Question_ID) → `legacy_d_rehabquestion` (ID), via ledger.

### Homs → `legacy_h_rehabanswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.

Relationship contracts: `FK_RehabAnswer_Question`: (Question_ID) → `legacy_h_rehabquestion` (ID), via ledger.

### Latakia → `legacy_l_rehabanswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.

Relationship contracts: `FK_RehabAnswer_Question`: (Question_ID) → `legacy_l_rehabquestion` (ID), via ledger.

### AlWadi → `legacy_w_rehabanswer`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_RehabAnswer_Question` (`Question_ID`)`.

Relationship contracts: `FK_RehabAnswer_Question`: (Question_ID) → `legacy_w_rehabquestion` (ID), via ledger.

## `rehabquestion`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_rehabquestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_rehabquestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_rehabquestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_rehabquestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_rehabquestion`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `rehabvisit`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_rehabvisit`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.

Relationship contracts: `FK_rehabvisit1`: (Visit_ID) → `legacy_a_visit` (ID), via ledger.

### Damascus → `legacy_d_rehabvisit`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.

Relationship contracts: `FK_rehabvisit1`: (Visit_ID) → `legacy_d_visit` (ID), via ledger.

### Homs → `legacy_h_rehabvisit`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.

Relationship contracts: `FK_rehabvisit1`: (Visit_ID) → `legacy_h_visit` (ID), via ledger.

### Latakia → `legacy_l_rehabvisit`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.

Relationship contracts: `FK_rehabvisit1`: (Visit_ID) → `legacy_l_visit` (ID), via ledger.

### AlWadi → `legacy_w_rehabvisit`

Identity: typed PK tuple. Columns: 32. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_rehabvisit1` (`Visit_ID`)`.

Relationship contracts: `FK_rehabvisit1`: (Visit_ID) → `legacy_w_visit` (ID), via ledger.

## `relation`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_relation`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_relation`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_relation`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_relation`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_relation`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `resultstable`

Uncertain/auxiliary; retain, investigate before disposition; disposition: retain every source column and row.

### Homs → `legacy_h_resultstable`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 89. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_resultstable`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 89. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

## `risk`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_risk`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_risk`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_risk`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_risk`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_risk`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `role`

Identity/configuration/audit/notification; disposition: retain every source column and row.

### Aleppo → `legacy_a_role`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Role_ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_role`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Role_ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_role`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Role_ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_role`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Role_ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_role`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Role_ID` | `int NOT NULL` |
| `Role_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Role_Code` | `int NOT NULL` |
| `Role_Describtion` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`Role_ID`)`.

Relationship contracts: No declared source foreign keys..

## `score`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_score`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_score`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_score`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_score`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_score`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `scorevalue`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_scorevalue`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.

Relationship contracts: `FK_scorevalue1`: (Score_ID) → `legacy_a_score` (ID), via ledger.

### Damascus → `legacy_d_scorevalue`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.

Relationship contracts: `FK_scorevalue1`: (Score_ID) → `legacy_d_score` (ID), via ledger.

### Homs → `legacy_h_scorevalue`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.

Relationship contracts: `FK_scorevalue1`: (Score_ID) → `legacy_h_score` (ID), via ledger.

### Latakia → `legacy_l_scorevalue`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.

Relationship contracts: `FK_scorevalue1`: (Score_ID) → `legacy_l_score` (ID), via ledger.

### AlWadi → `legacy_w_scorevalue`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Value` | `int NOT NULL` |
| `Score_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_scorevalue1` (`Score_ID`)`.

Relationship contracts: `FK_scorevalue1`: (Score_ID) → `legacy_w_score` (ID), via ledger.

## `specialcase`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_specialcase`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.

Relationship contracts: `FK_specialcase_microproject1`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_specialcase_microproject2`: (Type_ID) → `legacy_a_specialcasetype` (ID), via ledger; `FK_specialcase_microproject3`: (User_ID) → `legacy_a_user` (UserID), via ledger.

### Damascus → `legacy_d_specialcase`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.

Relationship contracts: `FK_specialcase_microproject1`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_specialcase_microproject2`: (Type_ID) → `legacy_d_specialcasetype` (ID), via ledger; `FK_specialcase_microproject3`: (User_ID) → `legacy_d_user` (UserID), via ledger.

### Homs → `legacy_h_specialcase`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.

Relationship contracts: `FK_specialcase_microproject1`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_specialcase_microproject2`: (Type_ID) → `legacy_h_specialcasetype` (ID), via ledger; `FK_specialcase_microproject3`: (User_ID) → `legacy_h_user` (UserID), via ledger.

### Latakia → `legacy_l_specialcase`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.

Relationship contracts: `FK_specialcase_microproject1`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_specialcase_microproject2`: (Type_ID) → `legacy_l_specialcasetype` (ID), via ledger; `FK_specialcase_microproject3`: (User_ID) → `legacy_l_user` (UserID), via ledger.

### AlWadi → `legacy_w_specialcase`

Identity: typed PK tuple. Columns: 8. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |
| `Type_ID` | `int DEFAULT NULL` |
| `Date` | `datetime DEFAULT NULL` |
| `Reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Action` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_specialcase_microproject1` (`MicroProject_ID`)`; `KEY `FK_specialcase_microproject2` (`Type_ID`)`; `KEY `FK_specialcase_microproject3` (`User_ID`)`.

Relationship contracts: `FK_specialcase_microproject1`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_specialcase_microproject2`: (Type_ID) → `legacy_w_specialcasetype` (ID), via ledger; `FK_specialcase_microproject3`: (User_ID) → `legacy_w_user` (UserID), via ledger.

## `specialcasetype`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_specialcasetype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_specialcasetype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_specialcasetype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_specialcasetype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_specialcasetype`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `state`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_state`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_state`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_state`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_state`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_state`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name_ar` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `statereason`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_statereason`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_statereason`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_statereason`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_statereason`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_statereason`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `street`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_street`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_street`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_street`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_street`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_street`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `subcategory`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_subcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.

Relationship contracts: `FK_Category_SubCategory`: (Category_ID) → `legacy_a_category` (ID), via ledger.

### Damascus → `legacy_d_subcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.

Relationship contracts: `FK_Category_SubCategory`: (Category_ID) → `legacy_d_category` (ID), via ledger.

### Homs → `legacy_h_subcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.

Relationship contracts: `FK_Category_SubCategory`: (Category_ID) → `legacy_h_category` (ID), via ledger.

### Latakia → `legacy_l_subcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.

Relationship contracts: `FK_Category_SubCategory`: (Category_ID) → `legacy_l_category` (ID), via ledger.

### AlWadi → `legacy_w_subcategory`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Category_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Category_SubCategory` (`Category_ID`)`.

Relationship contracts: `FK_Category_SubCategory`: (Category_ID) → `legacy_w_category` (ID), via ledger.

## `t1`

Uncertain/auxiliary; retain, investigate before disposition; disposition: retain every source column and row.

### Aleppo → `legacy_a_t1`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_t1`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Person_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int NOT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_t1`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_t1`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_t1`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 7. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `Person_ID` | `int DEFAULT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `AvgSal` | `double DEFAULT NULL` |
| `AvgEval` | `double DEFAULT NULL` |
| `workCount` | `bigint DEFAULT NULL` |
| `meCount` | `bigint DEFAULT NULL` |
| `EvalSal_Percentage` | `varchar(23) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

## `task`

Staff task workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_task`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`T_ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_task`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`T_ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_task`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`T_ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_task`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`T_ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_task`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `T_ID` | `int NOT NULL` |
| `T_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `T_State` | `int NOT NULL` |
| `T_Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`T_ID`)`.

Relationship contracts: No declared source foreign keys..

## `task_microproject`

Staff task workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_task_microproject`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_MicroProject`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_Task_MicroProject_Task`: (Task_ID) → `legacy_a_task` (T_ID), via ledger.

### Damascus → `legacy_d_task_microproject`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_MicroProject`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_Task_MicroProject_Task`: (Task_ID) → `legacy_d_task` (T_ID), via ledger.

### Homs → `legacy_h_task_microproject`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_MicroProject`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_Task_MicroProject_Task`: (Task_ID) → `legacy_h_task` (T_ID), via ledger.

### Latakia → `legacy_l_task_microproject`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_MicroProject`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_Task_MicroProject_Task`: (Task_ID) → `legacy_l_task` (T_ID), via ledger.

### AlWadi → `legacy_w_task_microproject`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TMP_ID` | `int NOT NULL` |
| `MicroProject_ID` | `int DEFAULT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `State` | `int NOT NULL` |
| `Date` | `datetime DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TMP_ID`)`; `KEY `FK_Task_MicroProject` (`MicroProject_ID`)`; `KEY `FK_Task_MicroProject_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_MicroProject`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_Task_MicroProject_Task`: (Task_ID) → `legacy_w_task` (T_ID), via ledger.

## `task_user`

Staff task workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_task_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_User_Task`: (Task_ID) → `legacy_a_task` (T_ID), via ledger; `FK_Task_User_User`: (User_ID) → `legacy_a_user` (UserID), via ledger.

### Damascus → `legacy_d_task_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_User_Task`: (Task_ID) → `legacy_d_task` (T_ID), via ledger; `FK_Task_User_User`: (User_ID) → `legacy_d_user` (UserID), via ledger.

### Homs → `legacy_h_task_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_User_Task`: (Task_ID) → `legacy_h_task` (T_ID), via ledger; `FK_Task_User_User`: (User_ID) → `legacy_h_user` (UserID), via ledger.

### Latakia → `legacy_l_task_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_User_Task`: (Task_ID) → `legacy_l_task` (T_ID), via ledger; `FK_Task_User_User`: (User_ID) → `legacy_l_user` (UserID), via ledger.

### AlWadi → `legacy_w_task_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `TU_ID` | `int NOT NULL` |
| `Task_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`TU_ID`)`; `KEY `FK_Task_User_User` (`User_ID`)`; `KEY `FK_Task_User_Task` (`Task_ID`)`.

Relationship contracts: `FK_Task_User_Task`: (Task_ID) → `legacy_w_task` (T_ID), via ledger; `FK_Task_User_User`: (User_ID) → `legacy_w_user` (UserID), via ledger.

## `temp_gender`

Uncertain/auxiliary; retain, investigate before disposition; disposition: retain every source column and row.

### Damascus → `legacy_d_temp_gender`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `varchar(9) DEFAULT NULL` |
| `sex` | `varchar(5) DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

## `user`

Identity/configuration/audit/notification; disposition: retain every source column and row.

### Aleppo → `legacy_a_user`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`UserID`)`; `UNIQUE KEY `UserName` (`UserName`)`; `KEY `FK_User_Role` (`UserRoleID`)`.

Relationship contracts: `FK_User_Role`: (UserRoleID) → `legacy_a_role` (Role_ID), via ledger.

### Damascus → `legacy_d_user`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`UserID`)`; `KEY `FK_User_Role` (`UserRoleID`)`.

Relationship contracts: `FK_User_Role`: (UserRoleID) → `legacy_d_role` (Role_ID), via ledger.

### Homs → `legacy_h_user`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`UserID`)`; `KEY `FK_User_Role` (`UserRoleID`)`.

Relationship contracts: `FK_User_Role`: (UserRoleID) → `legacy_h_role` (Role_ID), via ledger.

### Latakia → `legacy_l_user`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`UserID`)`; `UNIQUE KEY `UserName` (`UserName`)`; `KEY `FK_User_Role` (`UserRoleID`)`.

Relationship contracts: `FK_User_Role`: (UserRoleID) → `legacy_l_role` (Role_ID), via ledger.

### AlWadi → `legacy_w_user`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`UserID`)`; `KEY `FK_User_Role` (`UserRoleID`)`.

Relationship contracts: `FK_User_Role`: (UserRoleID) → `legacy_w_role` (Role_ID), via ledger.

## `user_notification`

Identity/configuration/audit/notification; disposition: retain every source column and row.

### Aleppo → `legacy_a_user_notification`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.

Relationship contracts: `FK_Micro_Notification`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger; `FK_Receiver_Notification`: (Sender_ID) → `legacy_a_user` (UserID), via ledger; `FK_User_Notification`: (User_ID) → `legacy_a_user` (UserID), via ledger.

### Damascus → `legacy_d_user_notification`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.

Relationship contracts: `FK_Micro_Notification`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger; `FK_Receiver_Notification`: (Sender_ID) → `legacy_d_user` (UserID), via ledger; `FK_User_Notification`: (User_ID) → `legacy_d_user` (UserID), via ledger.

### Homs → `legacy_h_user_notification`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.

Relationship contracts: `FK_Micro_Notification`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger; `FK_Receiver_Notification`: (Sender_ID) → `legacy_h_user` (UserID), via ledger; `FK_User_Notification`: (User_ID) → `legacy_h_user` (UserID), via ledger.

### Latakia → `legacy_l_user_notification`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.

Relationship contracts: `FK_Micro_Notification`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger; `FK_Receiver_Notification`: (Sender_ID) → `legacy_l_user` (UserID), via ledger; `FK_User_Notification`: (User_ID) → `legacy_l_user` (UserID), via ledger.

### AlWadi → `legacy_w_user_notification`

Identity: typed PK tuple. Columns: 10. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_User_Notification` (`User_ID`)`; `KEY `FK_Receiver_Notification` (`Sender_ID`)`; `KEY `FK_Micro_Notification` (`MicroProject_ID`)`.

Relationship contracts: `FK_Micro_Notification`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger; `FK_Receiver_Notification`: (Sender_ID) → `legacy_w_user` (UserID), via ledger; `FK_User_Notification`: (User_ID) → `legacy_w_user` (UserID), via ledger.

## `version`

Identity/configuration/audit/notification; disposition: retain every source column and row.

### Aleppo → `legacy_a_version`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_version`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_version`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_version`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_version`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `id` | `int NOT NULL` |
| `l_version` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`id`)`; `UNIQUE KEY `l_version` (`l_version`)`.

Relationship contracts: No declared source foreign keys..

## `visit`

Core/workflow record; disposition: retain every source column and row.

### Aleppo → `legacy_a_visit`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Visit_MicroProject`: (MicroProject_ID) → `legacy_a_microproject` (MP_ID), via ledger.

### Damascus → `legacy_d_visit`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Visit_MicroProject`: (MicroProject_ID) → `legacy_d_microproject` (MP_ID), via ledger.

### Homs → `legacy_h_visit`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Visit_MicroProject`: (MicroProject_ID) → `legacy_h_microproject` (MP_ID), via ledger.

### Latakia → `legacy_l_visit`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Visit_MicroProject`: (MicroProject_ID) → `legacy_l_microproject` (MP_ID), via ledger.

### AlWadi → `legacy_w_visit`

Identity: typed PK tuple. Columns: 23. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
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

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_Visit_MicroProject` (`MicroProject_ID`)`.

Relationship contracts: `FK_Visit_MicroProject`: (MicroProject_ID) → `legacy_w_microproject` (MP_ID), via ledger.

## `visit_schedule_temp`

Uncertain/auxiliary; retain, investigate before disposition; disposition: retain every source column and row.

### Homs → `legacy_h_visit_schedule_temp`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `MicroProject_ID` | `int DEFAULT NULL` |
| `beneficiary` | `varchar(28) DEFAULT NULL` |
| `visit_type` | `varchar(14) DEFAULT NULL` |
| `date` | `varchar(10) DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

## `visit_user`

Relationship/detail workflow; disposition: retain every source column and row.

### Aleppo → `legacy_a_visit_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.

Relationship contracts: `FK_visit_user1`: (Visit_ID) → `legacy_a_visit` (ID), via ledger; `FK_visit_user2`: (User_ID) → `legacy_a_user` (UserID), via ledger.

### Damascus → `legacy_d_visit_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.

Relationship contracts: `FK_visit_user1`: (Visit_ID) → `legacy_d_visit` (ID), via ledger; `FK_visit_user2`: (User_ID) → `legacy_d_user` (UserID), via ledger.

### Homs → `legacy_h_visit_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.

Relationship contracts: `FK_visit_user1`: (Visit_ID) → `legacy_h_visit` (ID), via ledger; `FK_visit_user2`: (User_ID) → `legacy_h_user` (UserID), via ledger.

### Latakia → `legacy_l_visit_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.

Relationship contracts: `FK_visit_user1`: (Visit_ID) → `legacy_l_visit` (ID), via ledger; `FK_visit_user2`: (User_ID) → `legacy_l_user` (UserID), via ledger.

### AlWadi → `legacy_w_visit_user`

Identity: typed PK tuple. Columns: 3. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Visit_ID` | `int DEFAULT NULL` |
| `User_ID` | `int DEFAULT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visit_user1` (`Visit_ID`)`; `KEY `FK_visit_user2` (`User_ID`)`.

Relationship contracts: `FK_visit_user1`: (Visit_ID) → `legacy_w_visit` (ID), via ledger; `FK_visit_user2`: (User_ID) → `legacy_w_user` (UserID), via ledger.

## `visitanswers`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_visitanswers`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.

Relationship contracts: `FK_visitanswers1`: (Question_ID) → `legacy_a_visitquestions` (ID), via ledger.

### Damascus → `legacy_d_visitanswers`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.

Relationship contracts: `FK_visitanswers1`: (Question_ID) → `legacy_d_visitquestions` (ID), via ledger.

### Homs → `legacy_h_visitanswers`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.

Relationship contracts: `FK_visitanswers1`: (Question_ID) → `legacy_h_visitquestions` (ID), via ledger.

### Latakia → `legacy_l_visitanswers`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.

Relationship contracts: `FK_visitanswers1`: (Question_ID) → `legacy_l_visitquestions` (ID), via ledger.

### AlWadi → `legacy_w_visitanswers`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Question_ID` | `int NOT NULL` |
| `Value` | `int NOT NULL DEFAULT '-1'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`; `KEY `FK_visitanswers1` (`Question_ID`)`.

Relationship contracts: `FK_visitanswers1`: (Question_ID) → `legacy_w_visitquestions` (ID), via ledger.

## `visitquestions`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_visitquestions`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_visitquestions`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_visitquestions`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_visitquestions`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_visitquestions`

Identity: typed PK tuple. Columns: 5. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `HasSelectedValue` | `int NOT NULL DEFAULT '0'` |
| `QNumber` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

## `work`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_work`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `W_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`W_ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_work`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `W_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`W_ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_work`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `W_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`W_ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_work`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `W_Name` | `longtext COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`W_ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_work`

Identity: typed PK tuple. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `W_Name` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `W_ID` | `int NOT NULL` |

Indexes: `UNIQUE KEY `legacy_primary` (`W_ID`)`.

Relationship contracts: No declared source foreign keys..

## `work_exp`

Uncertain/auxiliary; retain, investigate before disposition; disposition: retain every source column and row.

### Damascus → `legacy_d_work_exp`

Identity: immutable snapshot/table INSERT tuple ordinal. Columns: 2. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `PMP_ID` | `int DEFAULT NULL` |
| `P_WorkYears` | `int DEFAULT NULL` |

Indexes: No original indexes; only common registry indexes..

Relationship contracts: No declared source foreign keys..

## `worktype`

Reference/lookup/questionnaire definition; retain source-qualified; disposition: retain every source column and row.

### Aleppo → `legacy_a_worktype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Damascus → `legacy_d_worktype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Homs → `legacy_h_worktype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### Latakia → `legacy_l_worktype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..

### AlWadi → `legacy_w_worktype`

Identity: typed PK tuple. Columns: 4. Original PK becomes a UNIQUE constraint; common record PK/FK applies.

| Target column | Type, nullability, defaults, collation |
|---|---|
| `ID` | `int NOT NULL` |
| `Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL` |
| `Mark` | `int NOT NULL DEFAULT '0'` |

Indexes: `UNIQUE KEY `legacy_primary` (`ID`)`.

Relationship contracts: No declared source foreign keys..
