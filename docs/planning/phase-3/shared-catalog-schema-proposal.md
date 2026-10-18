# Whole-identical operational table sharing: physical addendum

**Proposed; physical confirmation pending. No sharing migration or runtime conversion has been written.** The user selected whole identical tables. This addendum changes the earlier per-source operational payload approach only for audited complete-table equality groups.

Frozen five-city audit verified all 616 source-table instances, 127 names, 3,947 columns and 1,721,699 tuple occurrences. Source archives and every parsed count matched their recorded baselines. See [the aggregate audit](shared-data-candidate-audit.md). The complete physical delta and group fingerprints are in [shared-catalog-schema.json](shared-catalog-schema.json); regenerate with build-shared-catalog-schema.py.

## Concrete outcome

The eligible operational scope is **55 table names / 58 equality groups**. Their 4,575 city-scoped operational rows become aliases for **1,145 canonical typed payload rows**, removing 3,430 duplicate operational payload occurrences. Original immutable preservation copies and all original source/snapshot keys remain intact. Equality groups with different city sets or payloads remain separate even when table names match. No matching subset of a differing table is shared.

| Requested table | Canonical rows | Scoped aliases | Result |
|---|---:|---:|---|
| category | 6 | 30 | One shared complete definition table for all five cities |
| subcategory | 17 | 85 | One shared complete definition table; category parent closure matches |
| task | 39 | 195 | One shared task definition set; city task assignments/completion remain independent |
| task_microproject / task_user | none | Existing city rows unchanged | Whole tables differ and are excluded |

The audit also found three nonempty equality groups in preservation-only databases, resultstable and version. Those are excluded from this operational addendum: configuration and uncertain auxiliary data are not silently promoted into active shared business tables. user remains preservation-only; no credentials or global login identities are merged. All 20 identical-empty groups are excluded: no existing rows can be consolidated, and empty equality does not establish future shared-write behavior.

## Identity, membership and exact provenance

Each eligible `shared_<name>` stores the exact typed original `c_*` payload once per canonical row. Existing `op_<name>` rows retain their operational entity IDs, city, source, source-table discriminator, historical origins and resolved local relationship columns. The existing composite city/source FKs therefore remain effective. They become scoped aliases through three nullable columns: shared_group_id, shared_row_id and shared_snapshot_id. No whole table is renamed or dropped, and no global legacy-ID uniqueness is introduced.

A composite membership FK proves the alias city/source/table/snapshot/group. A typed canonical group/row FK proves the shared row belongs to that group and concrete payload table. An exact origin FK proves the existing operational entity has an original occurrence in that same source/table/snapshot. This requires one additional non-destructive UNIQUE(source_id,source_table_id,snapshot_id,entity_id) on operational_origins. Shared aliases cannot be fabricated for web-born entities without real preserved origins.

All three sharing pointers must be NULL together or non-NULL together. When shared, every original c_* payload column on the op alias must be NULL. The values live in the canonical payload and immutable preservation, while city/entity metadata remains scoped. A source NULL remains a canonical NULL; it is never replaced by a sentinel. Original ID fields are payload values in the canonical row, not the canonical technical ID. Reconciliation must verify the exact source-key-to-canonical-row match; database FK existence alone does not establish that two rows have the same values.

Canonical row IDs are technical BIGINT identities. row_ordinal is a deterministic sorted-multiset occurrence position; row_sha256 plus duplicate_ordinal preserves any identical-row multiplicities inside a whole table. Full byte/value comparison fails closed on a digest collision. No within-table duplicate removal is permitted. Row fingerprints are private operational metadata; this proposal emits no source rows or individual-row hashes.

## Whole-table publication and reading

Groups and memberships freeze the audited schema contract, full-table fingerprint, row count, source/city, exact snapshot and verification evidence. Full-table SHA-256 uses the documented SQL-token multiset algorithm from the audit. This strict fingerprint is tied to that extraction version and source evidence; stored FLOAT/text values are independently compared under the preservation rules instead of assuming a database-rendered string reproduces dump token spelling. Different defaults and original sequence counters remain source provenance and do not automatically become a global future-write policy.

Prepare complete canonical payloads as staging data. Independently reconcile every member source table, its entire current operational projection, exact origin mapping and all declared/application-only parent relationships. Full-table count and payload equality must pass for every member before publication. A differing or newly edited member disqualifies the entire group; no partial rows or selected matching IDs may publish.

For publication, serialize against writers and lock every affected source/table scope in deterministic order. In one transaction, verify the unchanged frozen baseline, create all memberships, link every alias to its exact canonical row/origin snapshot, clear every alias c_* column, verify all complete-table projections and counts, then mark the group published with independent evidence and commit. Readers never observe a partially converted group. A crash leaves either the prior complete scoped payloads or the fully published group. Staging rows without aliases are not visible as live catalog content. Whole-table completeness and publication immutability are cross-row transaction/reconciliation contracts; CHECKs do not falsely claim to enforce aggregate equality.

Read permission is checked on the op alias and current city capability first. Read canonical values through the alias only if its membership/group is verified and published; use a conditional projection choosing canonical c_* when shared_row_id is present, otherwise the original op payload. Historical local resolved references still point to the proper same-city/source parent alias. A shared row ID does not grant another city or authorize a global catalog endpoint. Importer, reconciler and future domain readers must use this projection before conversion is enabled.

Canonical definitions are read-only after publication. This addendum grants no cross-city administration or new write permissions. Shared task definitions do not share task_user or task_microproject assignments, completion, recipients or audit actors. Changes to published definitions, membership, later snapshots or unsharing require an explicit reviewed forward operation and a separate business-write policy. The migration itself performs no data conversion and leaves every existing sharing pointer NULL.

## Physical rules and migration boundary

This proposal adds **57 tables**: 55 typed canonical payload tables and two group/membership support tables. It alters 55 op tables by adding three nullable BIGINT pointers, three composite indexes, three RESTRICT FKs and two CHECKs each. It adds one referenced unique index to operational_origins. No existing payload definition, local FK, source identity, credential, project numbering contract or preservation table is changed.

All tables use InnoDB / utf8mb4_bin, with each original payload column retaining its approved exact type/charset/collation/nullability/default. Technical IDs are BIGINT UNSIGNED; timestamps are explicit UTC DATETIME(6). No implicit timestamps or destructive cascades. The largest planned key is 80 bytes, the largest canonical table has 13 columns, and the largest resulting op table has seven indexes. All planned names fit the 64-byte identifier limit. New schema objects receive deterministic bounded names during migration implementation.

Rollback must inspect every canonical/group/membership table and every shared pointer before its first DDL and refuse any sharing evidence. Published data cannot be removed by migration rollback; a separately reviewed forward unsharing operation would be required. After explicit physical confirmation, migration implementation and independent MySQL constraint/lifecycle tests come before the full-table conversion engine and isolated reconciliation.

The database architect skill requires physical confirmation before migration code. Confirm the 55-name/58-group scope, one canonical payload plus city-scoped aliases, 57 added tables, 55 alias-table alterations and the operational_origins unique index. This draft does not claim that confirmation or execution has occurred.

## Included nonempty equality groups

| Table | Equal city members | Canonical rows | Alias rows |
|---|---|---:|---:|
| aganswer | Aleppo, Damascus, Homs, Latakia, AlWadi | 53 | 265 |
| age | Aleppo, Damascus, Homs, Latakia, AlWadi | 6 | 30 |
| agquestion | Aleppo, Damascus, Homs, Latakia, AlWadi | 14 | 70 |
| aid | Aleppo, Latakia, AlWadi | 20 | 60 |
| aidperiod | Aleppo, Damascus, Homs, Latakia, AlWadi | 7 | 35 |
| asset | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 20 |
| assetbenefits | Aleppo, Damascus, Homs, Latakia, AlWadi | 3 | 15 |
| assettype | Aleppo, Damascus, Homs, Latakia, AlWadi | 7 | 35 |
| backgrounds | Aleppo, Damascus, Homs, Latakia, AlWadi | 1 | 5 |
| career | Homs, AlWadi | 289 | 578 |
| category | Aleppo, Damascus, Homs, Latakia, AlWadi | 6 | 30 |
| checklist_opinion | Aleppo, Damascus, Homs, Latakia, AlWadi | 3 | 15 |
| course | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 20 |
| donor | Aleppo, Latakia | 15 | 30 |
| educationlevel | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 20 |
| educationtype | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 25 |
| fundtype | Aleppo, Damascus, Homs, Latakia, AlWadi | 2 | 10 |
| goal | Aleppo, Damascus, Homs, Latakia, AlWadi | 15 | 75 |
| illness | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 25 |
| imagetype | Aleppo, Damascus, Homs, Latakia, AlWadi | 12 | 60 |
| incomesource | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 20 |
| language | Aleppo, Damascus, Homs, Latakia, AlWadi | 11 | 55 |
| level | Aleppo, Damascus, Latakia | 4 | 12 |
| level | Homs, AlWadi | 4 | 8 |
| loss | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 25 |
| manualfile | Aleppo, Latakia | 1 | 2 |
| maritalstatus | Aleppo, Damascus, Homs, Latakia, AlWadi | 7 | 35 |
| meanswer | Aleppo, Damascus, Homs, Latakia, AlWadi | 104 | 520 |
| medicalneeds | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 25 |
| meprofitmarks | Aleppo, Latakia | 5 | 10 |
| meprofitmarks | Damascus, Homs, AlWadi | 5 | 15 |
| mequestion | Aleppo, Damascus, Homs, Latakia, AlWadi | 42 | 210 |
| meresultranges | Aleppo, Damascus, Homs, Latakia, AlWadi | 4 | 20 |
| microprojectsubtype | Aleppo, Damascus, Homs, Latakia, AlWadi | 17 | 85 |
| microprojecttype | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 25 |
| militarystate | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 25 |
| parish | Aleppo, Damascus, Homs, Latakia, AlWadi | 11 | 55 |
| personstate | Aleppo, Damascus, Homs, Latakia, AlWadi | 8 | 40 |
| powersource | Aleppo, Damascus, Homs, Latakia, AlWadi | 7 | 35 |
| profession_option | Aleppo, Damascus, Homs, Latakia, AlWadi | 44 | 220 |
| profession_question | Aleppo, Damascus, Homs, Latakia, AlWadi | 22 | 110 |
| profession_section | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 25 |
| rehabanswer | Aleppo, Damascus, Homs, Latakia, AlWadi | 56 | 280 |
| rehabquestion | Aleppo, Damascus, Homs, Latakia, AlWadi | 24 | 120 |
| relation | Aleppo, Damascus, Homs, Latakia, AlWadi | 27 | 135 |
| risk | Aleppo, Damascus, Homs, Latakia, AlWadi | 3 | 15 |
| role | Aleppo, Damascus, Homs, Latakia, AlWadi | 8 | 40 |
| score | Aleppo, Damascus, Homs, Latakia, AlWadi | 10 | 50 |
| scorevalue | Aleppo, Damascus, Homs, Latakia, AlWadi | 30 | 150 |
| specialcasetype | Aleppo, Damascus, Homs, Latakia, AlWadi | 2 | 10 |
| state | Aleppo, Damascus, Homs, Latakia, AlWadi | 11 | 55 |
| statereason | Damascus, Latakia | 25 | 50 |
| statereason | Homs, AlWadi | 25 | 50 |
| subcategory | Aleppo, Damascus, Homs, Latakia, AlWadi | 17 | 85 |
| task | Aleppo, Damascus, Homs, Latakia, AlWadi | 39 | 195 |
| visitanswers | Aleppo, Damascus, Homs, Latakia, AlWadi | 43 | 215 |
| visitquestions | Aleppo, Damascus, Homs, Latakia, AlWadi | 20 | 100 |
| worktype | Aleppo, Damascus, Homs, Latakia, AlWadi | 5 | 25 |

## Complete new-table physical breakdown

Every column definition, key, FK, CHECK and index purpose follows. No implicit Laravel timestamps or other columns.

### shared_catalog_groups

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `schema_contract_sha256` | `BINARY(32) NOT NULL` |
| `full_table_sha256` | `BINARY(32) NOT NULL` |
| `row_count` | `BIGINT UNSIGNED NOT NULL` |
| `status` | `VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'staging'` |
| `decision_locator` | `VARBINARY(1024) NOT NULL` |
| `verified_evidence_locator` | `VARBINARY(1024) NULL DEFAULT NULL` |
| `created_at` | `DATETIME(6) NOT NULL` |
| `published_at` | `DATETIME(6) NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical whole-table equality group.
- UNIQUE `(id,source_table_id)`: Typed canonical group discriminator.
- UNIQUE `(id,source_table_id,row_count)`: Membership baseline row count equality.
- UNIQUE `(source_table_id,schema_contract_sha256,full_table_sha256,row_count)`: One verified immutable whole-table group per exact fingerprint.
- INDEX `(source_table_id,status)`: Catalog FK and publication state lookup.

Foreign keys:
- `(source_table_id)` → `source_tables(id)`; RESTRICT on delete/update.

Checks:
- `status IN ('staging','published')`.
- `row_count > 0`.
- `(status = 'staging' AND published_at IS NULL AND verified_evidence_locator IS NULL) OR (status = 'published' AND published_at IS NOT NULL AND verified_evidence_locator IS NOT NULL)`.
- `source_table_id IN (1,2,3,4,5,7,8,9,10,11,12,15,17,20,23,24,28,29,30,32,33,34,35,38,39,40,42,43,44,45,46,58,59,60,62,78,79,92,93,94,97,98,100,102,103,104,105,107,108,109,111,113,123,124,127)`.

### shared_catalog_memberships

| Column | Exact type/nullability/default |
|---|---|
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `city_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `snapshot_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_count` | `BIGINT UNSIGNED NOT NULL` |
| `verified_at` | `DATETIME(6) NOT NULL` |
| `evidence_locator` | `VARBINARY(1024) NOT NULL` |

Indexes:
- PRIMARY `(source_id,source_table_id,snapshot_id)`: One whole-table group per exact source table snapshot.
- UNIQUE `(city_id,source_id,source_table_id,snapshot_id,group_id)`: Referenced membership proves full alias source/city/table/snapshot/group scope.
- INDEX `(group_id,source_table_id,row_count)`: Exact group kind and full-table count FK.
- INDEX `(source_id,city_id)`: Source/city FK.
- INDEX `(source_id,snapshot_id)`: Exact source snapshot FK.

Foreign keys:
- `(group_id,source_table_id,row_count)` → `shared_catalog_groups(id,source_table_id,row_count)`; RESTRICT on delete/update.
- `(source_id,city_id)` → `sources(id,city_id)`; RESTRICT on delete/update.
- `(source_id,snapshot_id)` → `snapshots(source_id,id)`; RESTRICT on delete/update.

Checks:
- `row_count > 0`.

### shared_aganswer

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Question_ID` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 1`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_age

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Max` | `int NULL DEFAULT NULL` |
| `c_Min` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_isActive` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 2`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_agquestion

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 3`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_aid

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 4`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_aidperiod

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 5`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_asset

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 7`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_assetbenefits

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 8`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_assettype

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_HomeMark` | `int NULL DEFAULT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_ProjectMark` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 9`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_backgrounds

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Image` | `longblob NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 10`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_career

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_SubCategory_ID` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 11`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_category

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 12`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_checklist_opinion

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Index` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 15`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_course

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 17`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_donor

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_abb` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 20`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_educationlevel

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 23`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_educationtype

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 24`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_fundtype

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 28`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_goal

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 29`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_illness

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 30`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_imagetype

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_Category` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 32`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_incomesource

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 33`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_language

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_L_ID` | `int NULL DEFAULT NULL` |
| `c_L_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 34`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_level

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_Level_Description` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Level_ID` | `int NULL DEFAULT NULL` |
| `c_Level_Symbol` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 35`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_loss

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 38`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_manualfile

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_Content` | `longblob NULL DEFAULT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Path` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 39`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_maritalstatus

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 40`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_meanswer

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Question_ID` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 42`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_medicalneeds

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 43`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_meprofitmarks

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_IsActive` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Max` | `float NULL DEFAULT NULL` |
| `c_Min` | `float NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 44`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_mequestion

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Type` | `varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 45`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_meresultranges

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_Evaluation` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Evaluation_en` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Max` | `int NULL DEFAULT NULL` |
| `c_Min` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 46`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_microprojectsubtype

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Type_ID` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 58`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_microprojecttype

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 59`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_militarystate

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 60`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_parish

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name_ar` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 62`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_personstate

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 78`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_powersource

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 79`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_profession_option

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_Has_Details` | `tinyint(1) NULL DEFAULT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Is_Other` | `tinyint(1) NULL DEFAULT NULL` |
| `c_Question_ID` | `int NULL DEFAULT NULL` |
| `c_Sort_Order` | `smallint NULL DEFAULT NULL` |
| `c_Text_Ar` | `varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 92`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_profession_question

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_Answer_Type` | `varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Is_Active` | `tinyint(1) NULL DEFAULT NULL` |
| `c_Number` | `varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL` |
| `c_Section_ID` | `tinyint NULL DEFAULT NULL` |
| `c_Sort_Order` | `smallint NULL DEFAULT NULL` |
| `c_Text_Ar` | `varchar(700) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 93`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_profession_section

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `tinyint NULL DEFAULT NULL` |
| `c_Name_Ar` | `varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL` |
| `c_Sort_Order` | `tinyint NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 94`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_rehabanswer

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Question_ID` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 97`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_rehabquestion

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Type` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 98`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_relation

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 100`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_risk

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 102`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_role

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_Role_Code` | `int NULL DEFAULT NULL` |
| `c_Role_Describtion` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Role_ID` | `int NULL DEFAULT NULL` |
| `c_Role_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 103`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_score

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 104`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_scorevalue

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Score_ID` | `int NULL DEFAULT NULL` |
| `c_Value` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 105`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_specialcasetype

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 107`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_state

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name_ar` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 108`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_statereason

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 109`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_subcategory

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_Category_ID` | `int NULL DEFAULT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 111`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_task

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_T_ID` | `int NULL DEFAULT NULL` |
| `c_T_Name` | `varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_T_State` | `int NULL DEFAULT NULL` |
| `c_T_Type` | `varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 113`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_visitanswers

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Question_ID` | `int NULL DEFAULT NULL` |
| `c_Value` | `int NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 123`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_visitquestions

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_HasSelectedValue` | `int NULL DEFAULT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Name` | `mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_QNumber` | `int NULL DEFAULT NULL` |
| `c_Type` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 124`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

### shared_worktype

| Column | Exact type/nullability/default |
|---|---|
| `id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `group_id` | `BIGINT UNSIGNED NOT NULL` |
| `source_table_id` | `BIGINT UNSIGNED NOT NULL` |
| `row_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `row_sha256` | `BINARY(32) NOT NULL` |
| `duplicate_ordinal` | `BIGINT UNSIGNED NOT NULL` |
| `c_ID` | `int NULL DEFAULT NULL` |
| `c_Mark` | `int NULL DEFAULT NULL` |
| `c_Name` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |
| `c_Name_en` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL` |

Indexes:
- PRIMARY `(id)`: Canonical payload row identity scoped by concrete table.
- UNIQUE `(group_id,id)`: Alias FK keeps row inside its verified group.
- UNIQUE `(group_id,row_ordinal)`: Exactly one canonical row per deterministic frozen multiset ordinal.
- UNIQUE `(group_id,row_sha256,duplicate_ordinal)`: Retain distinct equal-value occurrences; digest collision comparison fails closed.
- INDEX `(group_id,source_table_id)`: Canonical row group/table discriminator FK.

Foreign keys:
- `(group_id,source_table_id)` → `shared_catalog_groups(id,source_table_id)`; RESTRICT on delete/update.

Checks:
- `source_table_id = 127`.
- `row_ordinal > 0`.
- `duplicate_ordinal > 0`.

## Complete existing-table alterations

### op_aganswer

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_aganswer(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Question_ID IS NULL)`.

### op_age

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_age(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Max IS NULL AND c_Min IS NULL AND c_Name IS NULL AND c_isActive IS NULL)`.

### op_agquestion

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_agquestion(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL)`.

### op_aid

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_aid(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_aidperiod

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_aidperiod(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_asset

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_asset(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Mark IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_assetbenefits

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_assetbenefits(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Mark IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_assettype

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_assettype(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_HomeMark IS NULL AND c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL AND c_ProjectMark IS NULL)`.

### op_backgrounds

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_backgrounds(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Image IS NULL AND c_Name IS NULL)`.

### op_career

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_career(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL AND c_SubCategory_ID IS NULL)`.

### op_category

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_category(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL)`.

### op_checklist_opinion

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_checklist_opinion(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Index IS NULL AND c_Name IS NULL)`.

### op_course

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_course(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL)`.

### op_donor

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_donor(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_abb IS NULL)`.

### op_educationlevel

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_educationlevel(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_educationtype

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_educationtype(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_fundtype

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_fundtype(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_goal

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_goal(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_illness

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_illness(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Mark IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_imagetype

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_imagetype(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_Category IS NULL AND c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_incomesource

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_incomesource(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL)`.

### op_language

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_language(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_L_ID IS NULL AND c_L_Name IS NULL)`.

### op_level

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_level(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_Level_Description IS NULL AND c_Level_ID IS NULL AND c_Level_Symbol IS NULL)`.

### op_loss

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_loss(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Mark IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_manualfile

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_manualfile(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_Content IS NULL AND c_ID IS NULL AND c_Path IS NULL)`.

### op_maritalstatus

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_maritalstatus(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Mark IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_meanswer

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_meanswer(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Mark IS NULL AND c_Name IS NULL AND c_Question_ID IS NULL)`.

### op_medicalneeds

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_medicalneeds(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_meprofitmarks

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_meprofitmarks(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_IsActive IS NULL AND c_Mark IS NULL AND c_Max IS NULL AND c_Min IS NULL)`.

### op_mequestion

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_mequestion(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Type IS NULL)`.

### op_meresultranges

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_meresultranges(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_Evaluation IS NULL AND c_Evaluation_en IS NULL AND c_ID IS NULL AND c_Max IS NULL AND c_Min IS NULL)`.

### op_microprojectsubtype

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_microprojectsubtype(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL AND c_Type_ID IS NULL)`.

### op_microprojecttype

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_microprojecttype(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_militarystate

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_militarystate(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_parish

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_parish(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name_ar IS NULL AND c_Name_en IS NULL)`.

### op_personstate

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_personstate(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL AND c_Type IS NULL)`.

### op_powersource

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_powersource(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Mark IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_profession_option

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_profession_option(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_Has_Details IS NULL AND c_ID IS NULL AND c_Is_Other IS NULL AND c_Question_ID IS NULL AND c_Sort_Order IS NULL AND c_Text_Ar IS NULL)`.

### op_profession_question

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_profession_question(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_Answer_Type IS NULL AND c_ID IS NULL AND c_Is_Active IS NULL AND c_Number IS NULL AND c_Section_ID IS NULL AND c_Sort_Order IS NULL AND c_Text_Ar IS NULL)`.

### op_profession_section

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_profession_section(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name_Ar IS NULL AND c_Sort_Order IS NULL)`.

### op_rehabanswer

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_rehabanswer(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Question_ID IS NULL)`.

### op_rehabquestion

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_rehabquestion(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Type IS NULL)`.

### op_relation

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_relation(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_risk

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_risk(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL)`.

### op_role

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_role(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_Role_Code IS NULL AND c_Role_Describtion IS NULL AND c_Role_ID IS NULL AND c_Role_Name IS NULL)`.

### op_score

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_score(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL)`.

### op_scorevalue

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_scorevalue(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Score_ID IS NULL AND c_Value IS NULL)`.

### op_specialcasetype

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_specialcasetype(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### op_state

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_state(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name_ar IS NULL AND c_Name_en IS NULL)`.

### op_statereason

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_statereason(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL)`.

### op_subcategory

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_subcategory(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_Category_ID IS NULL AND c_ID IS NULL AND c_Name IS NULL)`.

### op_task

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_task(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_T_ID IS NULL AND c_T_Name IS NULL AND c_T_State IS NULL AND c_T_Type IS NULL)`.

### op_visitanswers

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_visitanswers(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Name IS NULL AND c_Question_ID IS NULL AND c_Value IS NULL)`.

### op_visitquestions

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_visitquestions(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_HasSelectedValue IS NULL AND c_ID IS NULL AND c_Name IS NULL AND c_QNumber IS NULL AND c_Type IS NULL)`.

### op_worktype

Add columns:
- `shared_group_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_row_id BIGINT UNSIGNED NULL DEFAULT NULL`.
- `shared_snapshot_id BIGINT UNSIGNED NULL DEFAULT NULL`.

Add indexes:
- INDEX `(shared_group_id,shared_row_id)`: Canonical typed group-row FK.
- INDEX `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)`: Exact scoped whole-table membership FK.
- INDEX `(source_id,source_table_id,shared_snapshot_id,id)`: Existing entity must have exact preserved origin.

Add foreign keys:
- `(shared_group_id,shared_row_id)` → `shared_worktype(group_id,id)`; RESTRICT on delete/update.
- `(city_id,source_id,source_table_id,shared_snapshot_id,shared_group_id)` → `shared_catalog_memberships(city_id,source_id,source_table_id,snapshot_id,group_id)`; RESTRICT on delete/update.
- `(source_id,source_table_id,shared_snapshot_id,id)` → `operational_origins(source_id,source_table_id,snapshot_id,entity_id)`; RESTRICT on delete/update.

Add checks:
- `(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)`.
- `shared_row_id IS NULL OR (c_ID IS NULL AND c_Mark IS NULL AND c_Name IS NULL AND c_Name_en IS NULL)`.

### operational_origins

Add UNIQUE(source_id,source_table_id,snapshot_id,entity_id). No values or existing indexes change.

## Validation performed

The audit verifies all five immutable archive/SQL hashes and every one of 616 source-table tuple counts. The generator checks the expected 55 operational names/58 groups, exact source membership groups, table/column name uniqueness, referenced unique keys and index-prefix coverage for new tables. These are metadata checks; no sharing DDL, data conversion or reconciliation run has executed.
