"""Generate an unapproved sharing addendum from aggregate frozen audit evidence."""
import hashlib
import json
import re
from pathlib import Path

ROOT=Path(__file__).resolve().parent
BASE_PATH=ROOT/'operational-schema.json'
BASE=json.loads(BASE_PATH.read_text(encoding='utf-8'))
REPORT_PATH=ROOT/'shared-data-candidate-audit.md'
AUDIT=json.loads(re.search(r'```json\n(.*?)\n```',REPORT_PATH.read_text(encoding='utf-8'),re.S).group(1))
DOMAINS={t['source_table']:t for t in BASE['tables']}
MAPPINGS={(m['city'],m['source_table']):m for m in BASE['mappings']}
GROUPS=[g for g in AUDIT['nonempty_equal_groups'] if g['table'] in DOMAINS]
EXCLUDED=[g for g in AUDIT['nonempty_equal_groups'] if g['table'] not in DOMAINS]
NAMES=sorted({g['table'] for g in GROUPS})

def col(name,definition): return {'name':name,'definition':definition}
def ix(kind,columns,reason): return {'kind':kind,'columns':columns,'reason':reason}
def fk(columns,parent,parents):
    return {'columns':columns,'parent_table':parent,'parent_columns':parents,'actions':'ON DELETE RESTRICT ON UPDATE RESTRICT'}
def table(name,columns,indexes,fks,checks):
    return {'name':name,'engine':'InnoDB','charset':'utf8mb4','collation':'utf8mb4_bin','columns':columns,'indexes':indexes,'foreign_keys':fks,'checks':checks}

support=[table('shared_catalog_groups',[
    col('id','BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'),col('source_table_id','BIGINT UNSIGNED NOT NULL'),
    col('schema_contract_sha256','BINARY(32) NOT NULL'),col('full_table_sha256','BINARY(32) NOT NULL'),
    col('row_count','BIGINT UNSIGNED NOT NULL'),
    col('status',"VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'staging'"),
    col('decision_locator','VARBINARY(1024) NOT NULL'),col('verified_evidence_locator','VARBINARY(1024) NULL DEFAULT NULL'),
    col('created_at','DATETIME(6) NOT NULL'),col('published_at','DATETIME(6) NULL DEFAULT NULL')],
    [ix('PRIMARY',['id'],'Canonical whole-table equality group'),ix('UNIQUE',['id','source_table_id'],'Typed canonical group discriminator'),
     ix('UNIQUE',['id','source_table_id','row_count'],'Membership baseline row count equality'),
     ix('UNIQUE',['source_table_id','schema_contract_sha256','full_table_sha256','row_count'],'One verified immutable whole-table group per exact fingerprint'),
     ix('INDEX',['source_table_id','status'],'Catalog FK and publication state lookup')],
    [fk(['source_table_id'],'source_tables',['id'])],
    ["status IN ('staging','published')",'row_count > 0',
     "(status = 'staging' AND published_at IS NULL AND verified_evidence_locator IS NULL) OR (status = 'published' AND published_at IS NOT NULL AND verified_evidence_locator IS NOT NULL)",
     'source_table_id IN ('+','.join(str(next(m['source_table_id'] for m in BASE['mappings'] if m['source_table']==n)) for n in NAMES)+')']),
    table('shared_catalog_memberships',[
    col('group_id','BIGINT UNSIGNED NOT NULL'),col('city_id','BIGINT UNSIGNED NOT NULL'),
    col('source_id','BIGINT UNSIGNED NOT NULL'),col('source_table_id','BIGINT UNSIGNED NOT NULL'),
    col('snapshot_id','BIGINT UNSIGNED NOT NULL'),col('row_count','BIGINT UNSIGNED NOT NULL'),
    col('verified_at','DATETIME(6) NOT NULL'),col('evidence_locator','VARBINARY(1024) NOT NULL')],
    [ix('PRIMARY',['source_id','source_table_id','snapshot_id'],'One whole-table group per exact source table snapshot'),
     ix('UNIQUE',['city_id','source_id','source_table_id','snapshot_id','group_id'],'Referenced membership proves full alias source/city/table/snapshot/group scope'),
     ix('INDEX',['group_id','source_table_id','row_count'],'Exact group kind and full-table count FK'),
     ix('INDEX',['source_id','city_id'],'Source/city FK'),ix('INDEX',['source_id','snapshot_id'],'Exact source snapshot FK')],
    [fk(['group_id','source_table_id','row_count'],'shared_catalog_groups',['id','source_table_id','row_count']),
     fk(['source_id','city_id'],'sources',['id','city_id']),fk(['source_id','snapshot_id'],'snapshots',['source_id','id'])],
    ['row_count > 0'])]

canonical=[]; alterations=[]
for name in NAMES:
    op=DOMAINS[name]
    kind=next(m['source_table_id'] for m in BASE['mappings'] if m['source_table']==name)
    payload=[c.copy() for c in op['columns'] if c['name'].startswith('c_')]
    canonical.append(table('shared_'+name,[col('id','BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'),
        col('group_id','BIGINT UNSIGNED NOT NULL'),col('source_table_id','BIGINT UNSIGNED NOT NULL'),
        col('row_ordinal','BIGINT UNSIGNED NOT NULL'),col('row_sha256','BINARY(32) NOT NULL'),
        col('duplicate_ordinal','BIGINT UNSIGNED NOT NULL')]+payload,
        [ix('PRIMARY',['id'],'Canonical payload row identity scoped by concrete table'),
         ix('UNIQUE',['group_id','id'],'Alias FK keeps row inside its verified group'),
         ix('UNIQUE',['group_id','row_ordinal'],'Exactly one canonical row per deterministic frozen multiset ordinal'),
         ix('UNIQUE',['group_id','row_sha256','duplicate_ordinal'],'Retain distinct equal-value occurrences; digest collision comparison fails closed'),
         ix('INDEX',['group_id','source_table_id'],'Canonical row group/table discriminator FK')],
        [fk(['group_id','source_table_id'],'shared_catalog_groups',['id','source_table_id'])],
        ['source_table_id = '+str(kind),'row_ordinal > 0','duplicate_ordinal > 0']))
    fields=[c['name'] for c in payload]
    alterations.append({'table':op['name'],
        'add_columns':[col('shared_group_id','BIGINT UNSIGNED NULL DEFAULT NULL'),
                       col('shared_row_id','BIGINT UNSIGNED NULL DEFAULT NULL'),
                       col('shared_snapshot_id','BIGINT UNSIGNED NULL DEFAULT NULL')],
        'add_indexes':[ix('INDEX',['shared_group_id','shared_row_id'],'Canonical typed group-row FK'),
                       ix('INDEX',['city_id','source_id','source_table_id','shared_snapshot_id','shared_group_id'],'Exact scoped whole-table membership FK'),
                       ix('INDEX',['source_id','source_table_id','shared_snapshot_id','id'],'Existing entity must have exact preserved origin')],
        'add_foreign_keys':[fk(['shared_group_id','shared_row_id'],'shared_'+name,['group_id','id']),
                            fk(['city_id','source_id','source_table_id','shared_snapshot_id','shared_group_id'],'shared_catalog_memberships',['city_id','source_id','source_table_id','snapshot_id','group_id']),
                            fk(['source_id','source_table_id','shared_snapshot_id','id'],'operational_origins',['source_id','source_table_id','snapshot_id','entity_id'])],
        'add_checks':['(shared_group_id IS NULL AND shared_row_id IS NULL AND shared_snapshot_id IS NULL) OR (shared_group_id IS NOT NULL AND shared_row_id IS NOT NULL AND shared_snapshot_id IS NOT NULL)',
                      'shared_row_id IS NULL OR ('+' AND '.join(f+' IS NULL' for f in fields)+')'],
        'drop_columns':[],'drop_indexes':[],'data_changes_on_migration':'none; existing rows remain unshared with original payload unchanged'})

existing=[{'table':'operational_origins','add_indexes':[ix('UNIQUE',['source_id','source_table_id','snapshot_id','entity_id'],'Alias shared snapshot provenance references exact entity origin')],'data_changes':'none'}]
proposal={'status':'PROPOSED physical addendum; explicit confirmation required before migration code',
 'base_schema_sha256':hashlib.sha256(BASE_PATH.read_bytes()).hexdigest(),
 'audit_method':AUDIT['method'],'audit_source_row_occurrences':AUDIT['rows_verified'],
 'scope':'Only nonempty whole-identical operational tables; distinct city equality groups remain separate; no matching-subset or within-table deduplication',
 'candidate_groups':GROUPS,'excluded_archive_only_candidates':EXCLUDED,
 'empty_groups':'All 20 identical-empty groups excluded; no rows to share and no future shared-write inference',
 'support_tables':support,'canonical_tables':canonical,'operational_table_alterations':alterations,
 'existing_table_alterations':existing,
 'invariants':['Every original preservation occurrence, source key and operational entity ID remains unchanged',
 'Source/city scoped op rows and all resolved local relationship columns remain in place; canonical payload has no web permissions',
 'Shared alias has all legacy c_* payload fields NULL; values exist once in typed shared payload plus immutable source preservation',
 'Only complete exact schema/full-row multiset groups may publish atomically, after independent per-source reconciliation',
 'Canonical rows and memberships are immutable after publication; no cross-city administration or write feature is enabled',
 'Read shared payload only after authorizing scoped alias, validating membership and published group; no global unauthenticated catalog endpoint',
 'No automatic later-snapshot continuity or changing membership based on matching IDs',
 'Group fingerprints are verified against full bytes/values, not accepted from hashes without exact source proof',
 'Complete declared parent closure must match per candidate group; application-only parent contracts receive independent review before publication',
 'No configuration, credential/user or uncertain auxiliary preservation-only table is promoted to global operational data'],
 'publication_protocol':['Freeze accepted snapshot, schema, audit-group fingerprint and source memberships; reject any source-table payload or rowcount drift',
 'Load one canonical complete-table multiset for each group, retaining duplicate multiplicities and exact types',
 'Independently reconcile every member full table and declared/application parent closure; source scoped aliases keep their original IDs/ref states',
 'Single transaction locks every member table scope against writers, verifies all current alias values match baseline and total table cardinality is complete',
 'Insert verified memberships, link every member alias to canonical typed row and exact origin snapshot, clear every c_* field, independently verify all alias counts/projections, then publish group and commit atomically',
 'Any incomplete/differing member table blocks the entire group; no partial publication and no matching-subset sharing',
 'Readers continue authorizing op aliases per source/city and fetch canonical payload only through published membership'],
 'rollback':'Migration rollback refuses any canonical/group/membership/shared-pointer evidence before any DDL; unsharing published data requires a separate reviewed forward operation, never destructive rollback'}
stats={'canonical_names':len(NAMES),'canonical_groups':len(GROUPS),'support_tables':len(support),'altered_op_tables':len(alterations),
 'archive_only_excluded_names':len({g['table'] for g in EXCLUDED}),'archive_only_excluded_groups':len(EXCLUDED),
 'canonical_payload_rows':sum(g['rows_per_city'] for g in GROUPS),
 'scoped_alias_rows':sum(g['rows_per_city']*len(g['cities']) for g in GROUPS),
 'duplicate_payload_occurrences_eliminated':sum(g['saved_duplicate_occurrences'] for g in GROUPS),
 'max_canonical_columns':max(len(t['columns']) for t in canonical),
 'max_resulting_op_indexes':max(len(DOMAINS[t['table'][3:]]['indexes'])+len(t['add_indexes']) for t in alterations),
 'max_index_bytes':80}
proposal['counts']=stats
assert stats['canonical_names']==55 and stats['canonical_groups']==58
allphysical={t['name']:t for t in support+canonical+BASE['tables']+BASE['support_tables']}
for t in support+canonical:
    assert len(t['name'])<=64
    assert len({c['name'].casefold() for c in t['columns']})==len(t['columns'])
    for foreign in t['foreign_keys']:
        parent=allphysical.get(foreign['parent_table'])
        if parent:
            assert any(i['kind'] in ('PRIMARY','UNIQUE') and i['columns']==foreign['parent_columns'] for i in parent['indexes'])
        assert any(i['columns'][:len(foreign['columns'])]==foreign['columns'] for i in t['indexes'])
for a in alterations:
    assert not set(c['name'] for c in a['add_columns']) & set(c['name'] for c in DOMAINS[a['table'][3:]]['columns'])
    assert stats['max_resulting_op_indexes']<=64
(ROOT/'shared-catalog-schema.json').write_text(json.dumps(proposal,ensure_ascii=False,indent=2)+'\n',encoding='utf-8',newline='\n')
text=['# Whole-identical operational table sharing: physical addendum','','**Proposed; physical confirmation pending. No sharing migration or runtime conversion has been written.** The user selected whole identical tables. This addendum changes the earlier per-source operational payload approach only for audited complete-table equality groups.','',
      'Frozen five-city audit verified all 616 source-table instances, 127 names, 3,947 columns and 1,721,699 tuple occurrences. Source archives and every parsed count matched their recorded baselines. See [the aggregate audit](shared-data-candidate-audit.md). The complete physical delta and group fingerprints are in [shared-catalog-schema.json](shared-catalog-schema.json); regenerate with build-shared-catalog-schema.py.','',
      '## Concrete outcome','','The eligible operational scope is **55 table names / 58 equality groups**. Their 4,575 city-scoped operational rows become aliases for **1,145 canonical typed payload rows**, removing 3,430 duplicate operational payload occurrences. Original immutable preservation copies and all original source/snapshot keys remain intact. Equality groups with different city sets or payloads remain separate even when table names match. No matching subset of a differing table is shared.','',
      '| Requested table | Canonical rows | Scoped aliases | Result |','|---|---:|---:|---|',
      '| category | 6 | 30 | One shared complete definition table for all five cities |',
      '| subcategory | 17 | 85 | One shared complete definition table; category parent closure matches |',
      '| task | 39 | 195 | One shared task definition set; city task assignments/completion remain independent |',
      '| task_microproject / task_user | none | Existing city rows unchanged | Whole tables differ and are excluded |','',
      'The audit also found three nonempty equality groups in preservation-only databases, resultstable and version. Those are excluded from this operational addendum: configuration and uncertain auxiliary data are not silently promoted into active shared business tables. user remains preservation-only; no credentials or global login identities are merged. All 20 identical-empty groups are excluded: no existing rows can be consolidated, and empty equality does not establish future shared-write behavior.','',
      '## Identity, membership and exact provenance','','Each eligible `shared_<name>` stores the exact typed original `c_*` payload once per canonical row. Existing `op_<name>` rows retain their operational entity IDs, city, source, source-table discriminator, historical origins and resolved local relationship columns. The existing composite city/source FKs therefore remain effective. They become scoped aliases through three nullable columns: shared_group_id, shared_row_id and shared_snapshot_id. No whole table is renamed or dropped, and no global legacy-ID uniqueness is introduced.','',
      'A composite membership FK proves the alias city/source/table/snapshot/group. A typed canonical group/row FK proves the shared row belongs to that group and concrete payload table. An exact origin FK proves the existing operational entity has an original occurrence in that same source/table/snapshot. This requires one additional non-destructive UNIQUE(source_id,source_table_id,snapshot_id,entity_id) on operational_origins. Shared aliases cannot be fabricated for web-born entities without real preserved origins.','',
      'All three sharing pointers must be NULL together or non-NULL together. When shared, every original c_* payload column on the op alias must be NULL. The values live in the canonical payload and immutable preservation, while city/entity metadata remains scoped. A source NULL remains a canonical NULL; it is never replaced by a sentinel. Original ID fields are payload values in the canonical row, not the canonical technical ID. Reconciliation must verify the exact source-key-to-canonical-row match; database FK existence alone does not establish that two rows have the same values.','',
      'Canonical row IDs are technical BIGINT identities. row_ordinal is a deterministic sorted-multiset occurrence position; row_sha256 plus duplicate_ordinal preserves any identical-row multiplicities inside a whole table. Full byte/value comparison fails closed on a digest collision. No within-table duplicate removal is permitted. Row fingerprints are private operational metadata; this proposal emits no source rows or individual-row hashes.','',
      '## Whole-table publication and reading','','Groups and memberships freeze the audited schema contract, full-table fingerprint, row count, source/city, exact snapshot and verification evidence. Full-table SHA-256 uses the documented SQL-token multiset algorithm from the audit. This strict fingerprint is tied to that extraction version and source evidence; stored FLOAT/text values are independently compared under the preservation rules instead of assuming a database-rendered string reproduces dump token spelling. Different defaults and original sequence counters remain source provenance and do not automatically become a global future-write policy.','',
      'Prepare complete canonical payloads as staging data. Independently reconcile every member source table, its entire current operational projection, exact origin mapping and all declared/application-only parent relationships. Full-table count and payload equality must pass for every member before publication. A differing or newly edited member disqualifies the entire group; no partial rows or selected matching IDs may publish.','',
      'For publication, serialize against writers and lock every affected source/table scope in deterministic order. In one transaction, verify the unchanged frozen baseline, create all memberships, link every alias to its exact canonical row/origin snapshot, clear every alias c_* column, verify all complete-table projections and counts, then mark the group published with independent evidence and commit. Readers never observe a partially converted group. A crash leaves either the prior complete scoped payloads or the fully published group. Staging rows without aliases are not visible as live catalog content. Whole-table completeness and publication immutability are cross-row transaction/reconciliation contracts; CHECKs do not falsely claim to enforce aggregate equality.','',
      'Read permission is checked on the op alias and current city capability first. Read canonical values through the alias only if its membership/group is verified and published; use a conditional projection choosing canonical c_* when shared_row_id is present, otherwise the original op payload. Historical local resolved references still point to the proper same-city/source parent alias. A shared row ID does not grant another city or authorize a global catalog endpoint. Importer, reconciler and future domain readers must use this projection before conversion is enabled.','',
      'Canonical definitions are read-only after publication. This addendum grants no cross-city administration or new write permissions. Shared task definitions do not share task_user or task_microproject assignments, completion, recipients or audit actors. Changes to published definitions, membership, later snapshots or unsharing require an explicit reviewed forward operation and a separate business-write policy. The migration itself performs no data conversion and leaves every existing sharing pointer NULL.','',
      '## Physical rules and migration boundary','','This proposal adds **57 tables**: 55 typed canonical payload tables and two group/membership support tables. It alters 55 op tables by adding three nullable BIGINT pointers, three composite indexes, three RESTRICT FKs and two CHECKs each. It adds one referenced unique index to operational_origins. No existing payload definition, local FK, source identity, credential, project numbering contract or preservation table is changed.','',
      'All tables use InnoDB / utf8mb4_bin, with each original payload column retaining its approved exact type/charset/collation/nullability/default. Technical IDs are BIGINT UNSIGNED; timestamps are explicit UTC DATETIME(6). No implicit timestamps or destructive cascades. The largest planned key is 80 bytes, the largest canonical table has 13 columns, and the largest resulting op table has seven indexes. All planned names fit the 64-byte identifier limit. New schema objects receive deterministic bounded names during migration implementation.','',
      'Rollback must inspect every canonical/group/membership table and every shared pointer before its first DDL and refuse any sharing evidence. Published data cannot be removed by migration rollback; a separately reviewed forward unsharing operation would be required. After explicit physical confirmation, migration implementation and independent MySQL constraint/lifecycle tests come before the full-table conversion engine and isolated reconciliation.','',
      'The database architect skill requires physical confirmation before migration code. Confirm the 55-name/58-group scope, one canonical payload plus city-scoped aliases, 57 added tables, 55 alias-table alterations and the operational_origins unique index. This draft does not claim that confirmation or execution has occurred.','',
      '## Included nonempty equality groups','','| Table | Equal city members | Canonical rows | Alias rows |','|---|---|---:|---:|']
text += ['| '+g['table']+' | '+', '.join(g['cities'])+' | '+str(g['rows_per_city'])+' | '+str(g['rows_per_city']*len(g['cities']))+' |' for g in GROUPS]
text += ['','## Complete new-table physical breakdown','','Every column definition, key, FK, CHECK and index purpose follows. No implicit Laravel timestamps or other columns.','']
for t in support+canonical:
    text += ['### '+t['name'],'','| Column | Exact type/nullability/default |','|---|---|']
    text += ['| `'+c['name']+'` | `'+c['definition']+'` |' for c in t['columns']]
    text += ['', 'Indexes:']+['- '+i['kind']+' `('+','.join(i['columns'])+')`: '+i['reason']+'.' for i in t['indexes']]
    text += ['', 'Foreign keys:']+['- `('+','.join(f['columns'])+')` → `'+f['parent_table']+'('+','.join(f['parent_columns'])+')`; RESTRICT on delete/update.' for f in t['foreign_keys']]
    text += ['', 'Checks:']+['- `'+c+'`.' for c in t['checks']]+['']
text += ['## Complete existing-table alterations','']
for a in alterations:
    text += ['### '+a['table'],'','Add columns:']+['- `'+c['name']+' '+c['definition']+'`.' for c in a['add_columns']]
    text += ['', 'Add indexes:']+['- '+i['kind']+' `('+','.join(i['columns'])+')`: '+i['reason']+'.' for i in a['add_indexes']]
    text += ['', 'Add foreign keys:']+['- `('+','.join(f['columns'])+')` → `'+f['parent_table']+'('+','.join(f['parent_columns'])+')`; RESTRICT on delete/update.' for f in a['add_foreign_keys']]
    text += ['', 'Add checks:']+['- `'+c+'`.' for c in a['add_checks']]+['']
text += ['### operational_origins','','Add UNIQUE(source_id,source_table_id,snapshot_id,entity_id). No values or existing indexes change.','',
         '## Validation performed','','The audit verifies all five immutable archive/SQL hashes and every one of 616 source-table tuple counts. The generator checks the expected 55 operational names/58 groups, exact source membership groups, table/column name uniqueness, referenced unique keys and index-prefix coverage for new tables. These are metadata checks; no sharing DDL, data conversion or reconciliation run has executed.','']
(ROOT/'shared-catalog-schema-proposal.md').write_text('\n'.join(text),encoding='utf-8',newline='\n')
print(json.dumps(stats,indent=2))
