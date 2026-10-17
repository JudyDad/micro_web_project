"""Generate a review proposal from schema metadata only. Never reads business rows."""
import hashlib
import json
import re
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parent
PLAN = ROOT.parent
REPO = PLAN.parent.parent
MANIFEST = REPO / 'database/schema/preservation-v1.json'
SOURCE = PLAN / 'source-schemas.json'
manifest = json.loads(MANIFEST.read_text(encoding='utf-8'))
inventory = json.loads((PLAN / 'p0-2-data-inventory.json').read_text(encoding='utf-8'))
names = sorted({t['source_table'] for t in manifest['tables']})
table_ids = {name: i + 1 for i, name in enumerate(names)}
# Frozen source_tables IDs must agree with the migration's sorted catalog.
assert table_ids['user'] == 117
archive = {'dept', 'resultstable', 't1', 'temp_gender', 'visit_schedule_temp',
           'work_exp', 'databases', 'version', 'user'}
grouped = defaultdict(list)
for item in manifest['tables']:
    grouped[item['source_table']].append(item)

def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()

def storage_type(definition, ddl):
    # Defaults/nullability/auto-increment do not change storage representation.
    value = re.split(r'\s+(?:NOT NULL|DEFAULT|AUTO_INCREMENT)\b', definition, 1)[0]
    value = re.sub(r'\s+NULL$', '', value)
    if re.match(r'(?:var)?char\(|(?:tiny|medium|long)?text\b|enum\(', value):
        coll = re.search(r'COLLATE\s+(\w+)', value)
        charset = re.search(r'CHARACTER SET\s+(\w+)', value)
        if not charset:
            encoding = coll.group(1).split('_')[0] if coll else re.search(r'DEFAULT CHARSET=(\w+)', ddl).group(1)
            value = re.sub(r'\s+COLLATE', ' CHARACTER SET '+encoding+' COLLATE', value) if coll else value+' CHARACTER SET '+encoding
        if not coll:
            table_coll = re.search(r'COLLATE=(\w+)', ddl)
            value += ' COLLATE '+(table_coll.group(1) if table_coll else 'utf8mb4_bin')
    return value

def column(name, definition):
    return {'name': name, 'definition': definition}

def index(kind, columns, reason):
    return {'kind': kind, 'columns': columns, 'reason': reason}

def fk(columns, parent, parents):
    return {'columns': columns, 'parent_table': parent, 'parent_columns': parents,
            'actions': 'ON DELETE RESTRICT ON UPDATE RESTRICT'}

def table(name, columns, indexes, foreign_keys, checks=None):
    return {'name': name, 'engine': 'InnoDB', 'charset': 'utf8mb4', 'collation': 'utf8mb4_bin',
            'columns': columns, 'indexes': indexes, 'foreign_keys': foreign_keys, 'checks': checks or []}

tables, mappings, targets, field_map = [], [], {}, {}
for name, instances in sorted(grouped.items()):
    target = None if name in archive else 'op_'+name
    signatures = defaultdict(set)
    for t in instances:
        for c in t['columns']:
            signatures[c['source_column']].add(storage_type(c['target_definition'], t['original_ddl']))
    target_columns = {}
    for field, types in signatures.items():
        for typename in sorted(types):
            dest = 'c_'+field
            if len(types) > 1:
                dest += '__'+hashlib.sha256(typename.encode()).hexdigest()[:8]
            assert len(dest) <= 64
            target_columns[field, typename] = dest
    if target:
        cols = [column('id', 'BIGINT UNSIGNED NOT NULL'), column('city_id', 'BIGINT UNSIGNED NOT NULL'),
                column('source_id', 'BIGINT UNSIGNED NOT NULL'), column('source_table_id', 'BIGINT UNSIGNED NOT NULL')]
        for (field, typename), dest in sorted(target_columns.items()):
            # Legacy identifiers are absent on web-born rows; importer still enforces
            # original per-source nullability and presence without changing values.
            cols.append(column(dest, typename+' NULL DEFAULT NULL'))
        ix = [index('PRIMARY', ['id'], 'Same BIGINT identity as operational_entities; no second allocation'),
              index('UNIQUE', ['city_id', 'source_id', 'source_table_id', 'id'], 'Typed row and entity kind/city/source consistency')]
        fks = [fk(['city_id','source_id','source_table_id','id'], 'operational_entities', ['city_id','source_id','source_table_id','id'])]
        checks = [f'source_table_id = {table_ids[name]}']
        if name == 'microproject':
            mp = target_columns['MP_ID', storage_type(instances[0]['columns'][0]['target_definition'], instances[0]['original_ddl'])]
            # Locate by name rather than relying on column order in future metadata.
            mp = next(v for (f, _), v in target_columns.items() if f == 'MP_ID')
            ix.append(index('UNIQUE', ['source_id', mp], 'Original project number within its source; NULL prohibited below'))
            checks.append(mp+' IS NOT NULL')
        obj = table(target, cols, ix, fks, checks)
        obj['source_table'] = name
        obj['classification'] = inventory['tables'][name]['classification']
        obj['source_presence'] = {t['city']: [c['source_column'] for c in t['columns']] for t in instances}
        tables.append(obj)
        targets[name] = obj
    for t in instances:
        fields = []
        for c in t['columns']:
            typ = storage_type(c['target_definition'], t['original_ddl'])
            dest = target_columns[c['source_column'], typ] if target else None
            fields.append({'source_column': c['source_column'], 'source_definition': c['source_definition'],
                           'preservation_column': c['target_column'], 'target_column': dest,
                           'target_definition': typ+' NULL DEFAULT NULL' if target else None,
                           'disposition': 'exact typed value in preservation and shared operational column' if target else 'exact typed value in preservation; no duplicate operational payload'})
            if target:
                field_map[t['city'], name, c['source_column']] = dest
        mappings.append({'city': t['city'], 'source_table': name, 'preservation_table': t['target_table'],
                         'target_table': target, 'source_table_id': table_ids[name],
                         'disposition': 'shared operational row plus immutable typed preservation' if target else 'preservation-only; imported and independently reconciled in full',
                         'legacy_primary_key': t['legacy_primary_key'], 'columns': fields,
                         'source_index_definitions': t['target_indexes']})

# Each distinct relationship gets its own supplemental resolved ID and explicit state.
# Relationships into archived users resolve to preserved source-user occurrences.
relationships, signatures = [], {}
for t in manifest['tables']:
    for rel in t['source_foreign_keys']:
        signature = (t['source_table'], tuple(rel['child_columns']), rel['parent_table'], tuple(rel['parent_columns']))
        signatures.setdefault(signature, []).append((t['city'], rel['contract']))
application = [
    ('microproject_ag_answer', ('Question_ID',), 'agquestion', ('ID',)),
    ('microproject_ag_answer', ('Answer_ID',), 'aganswer', ('ID',)),
    ('education', ('Type_ID',), 'educationtype', ('ID',)),
    ('person_family', ('Person_ID',), 'person', ('P_ID',)),
    ('person_family', ('P_Provider_ID',), 'person', ('P_ID',)),
]
for signature in application:
    child, fields, parent, _ = signature
    for item in grouped[child]:
        if all(any(c['source_column'] == f for c in item['columns']) for f in fields):
            signatures.setdefault(signature, []).append((item['city'], 'application-only'))
for signature, occurrences in sorted(signatures.items()):
    child, fields, parent, parents = signature
    slug = hashlib.sha256(repr(signature).encode()).hexdigest()[:12]
    resolved = 'ref_'+slug+'_id'
    state = 'ref_'+slug+'_state'
    child_target = targets.get(child)
    parent_target = targets.get(parent)
    if child_target:
        child_target['columns'] += [column(resolved, 'BIGINT UNSIGNED NULL DEFAULT NULL'),
                                    column(state, "VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'pending'")]
        child_target['checks'] += [f"{state} IN ('pending','resolved','null','orphan','ambiguous','sentinel','not_applicable')",
                                    f"({state} = 'resolved' AND {resolved} IS NOT NULL) OR ({state} <> 'resolved' AND {resolved} IS NULL)"]
        # Same source/table prevents resolving a city A child to city B's record.
        if parent_target:
            child_target['indexes'].append(index('INDEX', ['city_id','source_id', resolved], 'Resolved same-city/source relationship'))
            child_target['foreign_keys'].append(fk(['city_id','source_id',resolved], parent_target['name'], ['city_id','source_id','id']))
        else:
            # Archived parents use a fixed table discriminator plus source_records.
            parent_kind = 'ref_'+slug+'_table'
            child_target['columns'].append(column(parent_kind, 'BIGINT UNSIGNED NOT NULL DEFAULT '+str(table_ids[parent])))
            child_target['checks'].append(parent_kind+' = '+str(table_ids[parent]))
            child_target['indexes'].append(index('INDEX', ['source_id', parent_kind, resolved], 'Resolved historical source-user occurrence'))
            child_target['foreign_keys'].append(fk(['source_id',parent_kind,resolved], 'source_records', ['source_id','table_id','id']))
        relationships.append({'id': slug, 'child_source_table': child, 'child_columns': list(fields),
                              'parent_source_table': parent, 'parent_columns': list(parents),
                              'occurrences': [{'city': c, 'contract': n} for c,n in sorted(set(occurrences))],
                              'target_table': child_target['name'], 'resolved_column': resolved,
                              'state_column': state, 'parent_target_table': parent_target['name'] if parent_target else 'source_records',
                              'rule': 'Source-compatible tuple comparison in same source and immutable snapshot; one match resolves; raw fields unchanged; non-null unresolved creates migration exception'})
    else:
        relationships.append({'id':slug, 'child_source_table':child, 'child_columns':list(fields),
                              'parent_source_table':parent,'parent_columns':list(parents),
                              'occurrences':[{'city':c,'contract':n} for c,n in sorted(set(occurrences))],
                              'target_table':None,'resolved_column':None,'state_column':None,
                              'parent_target_table':None,
                              'disposition':'Complete preservation-layer record_relationships ledger; no shared child payload',
                              'rule':'Exact same-source/snapshot preserved occurrences; retain unresolved raw values and migration exceptions'})

# Referenced composite keys must exist in exact order.
for obj in tables:
    obj['indexes'].insert(2, index('UNIQUE', ['city_id','source_id','id'], 'Referenced key for cross-table city/source FKs'))

support = [
    table('operational_entities', [column('id','BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'), column('city_id','BIGINT UNSIGNED NOT NULL'),
          column('source_id','BIGINT UNSIGNED NOT NULL'), column('source_table_id','BIGINT UNSIGNED NOT NULL'),
          column('identity_kind',"VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL"),
          column('first_key_sha256','BINARY(32) NULL DEFAULT NULL'), column('first_key_bytes','LONGBLOB NULL DEFAULT NULL'),
          column('created_at','DATETIME(6) NOT NULL')],
          [index('PRIMARY',['id'],'Stable entity ID'), index('UNIQUE',['city_id','source_id','source_table_id','id'],'Kind and scope for typed-row FK'),
           index('UNIQUE',['source_id','source_table_id','id'],'Kind and source for origin FK'),
           index('UNIQUE',['source_id','source_table_id','first_key_sha256'],'First import key retry lookup; full bytes compared; keyless/web NULL')],
          [fk(['source_id','city_id'],'sources',['id','city_id']), fk(['source_table_id'],'source_tables',['id'])],
          ["identity_kind IN ('legacy_primary','legacy_ordinal','web')", "(identity_kind = 'legacy_primary' AND first_key_sha256 IS NOT NULL AND first_key_bytes IS NOT NULL) OR (identity_kind <> 'legacy_primary' AND first_key_sha256 IS NULL AND first_key_bytes IS NULL)"]),
    table('operational_origins', [column('source_record_id','BIGINT UNSIGNED NOT NULL'), column('source_id','BIGINT UNSIGNED NOT NULL'),
          column('source_table_id','BIGINT UNSIGNED NOT NULL'), column('snapshot_id','BIGINT UNSIGNED NOT NULL'),
          column('entity_id','BIGINT UNSIGNED NOT NULL'), column('linked_by_run_id','BIGINT UNSIGNED NOT NULL'),
          column('linked_at','DATETIME(6) NOT NULL'), column('decision_locator','VARBINARY(1024) NULL DEFAULT NULL')],
          [index('PRIMARY',['source_record_id'],'One operational mapping per immutable occurrence'),
           index('UNIQUE',['entity_id','snapshot_id'],'One same-table occurrence per entity per snapshot; no duplicate row collapse'),
           index('INDEX',['source_id','source_table_id','snapshot_id','source_record_id'],'Composite source and snapshot origin constraint'),
           index('INDEX',['source_id','source_table_id','entity_id'],'Source and kind entity constraint'),
           index('INDEX',['linked_by_run_id'],'Import audit FK')],
          [fk(['source_id','source_table_id','snapshot_id','source_record_id'],'source_records',['source_id','table_id','snapshot_id','id']),
           fk(['source_id','source_table_id','entity_id'],'operational_entities',['source_id','source_table_id','id']),
           fk(['linked_by_run_id'],'migration_runs',['id'])]),
    table('migration_checkpoints', [column('id','BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'), column('run_id','BIGINT UNSIGNED NOT NULL'),
          column('source_table_id','BIGINT UNSIGNED NOT NULL'), column('phase','VARCHAR(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL'),
          column('cursor_bytes','LONGBLOB NULL DEFAULT NULL'), column('cursor_sha256','BINARY(32) NULL DEFAULT NULL'),
          column('committed_count','BIGINT UNSIGNED NOT NULL DEFAULT 0'), column('batch_sha256','BINARY(32) NULL DEFAULT NULL'),
          column('status',"VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'pending'"),
          column('updated_at','DATETIME(6) NOT NULL')],
          [index('PRIMARY',['id'],'Checkpoint ID'), index('UNIQUE',['run_id','source_table_id','phase'],'One atomic high water mark per pass/table/run'),
           index('INDEX',['source_table_id'],'Catalog FK')],
          [fk(['run_id'],'migration_runs',['id']), fk(['source_table_id'],'source_tables',['id'])],
          ["phase IN ('preservation','operational','relationships','attachments')", "status IN ('pending','running','complete','failed')",
           '(cursor_bytes IS NULL AND cursor_sha256 IS NULL) OR (cursor_bytes IS NOT NULL AND cursor_sha256 IS NOT NULL)']),
]

# Add the missing physical child indexes on the registry.
support[0]['indexes'] += [index('INDEX',['source_id','city_id'],'Source/city FK'), index('INDEX',['source_table_id'],'Catalog FK')]
support[0]['checks'].append('source_table_id IN ('+','.join(str(table_ids[n]) for n in sorted(targets))+')')

# A durable contract freezes every interpretation used by an import run. The
# run/snapshot composite FKs prevent a checkpoint or origin from citing another
# source snapshot's run. These are proposal definitions, never executable DDL.
run_contract = table('migration_run_contracts', [
    column('run_id','BIGINT UNSIGNED NOT NULL'), column('snapshot_id','BIGINT UNSIGNED NOT NULL'),
    column('frozen_manifest_sha256','BINARY(32) NOT NULL'), column('mapping_sha256','BINARY(32) NOT NULL'),
    column('schema_sha256','BINARY(32) NOT NULL'), column('extractor_sha256','BINARY(32) NOT NULL'),
    column('extractor_version','VARCHAR(100) NOT NULL'), column('key_encoding_version','VARCHAR(32) NOT NULL'),
    column('created_at','DATETIME(6) NOT NULL')],
    [index('PRIMARY',['run_id'],'One immutable interpretation contract per run'),
     index('UNIQUE',['run_id','snapshot_id','mapping_sha256'],'Exact checkpoint interpretation and snapshot FK'),
     index('INDEX',['snapshot_id','run_id'],'Exact run/snapshot FK')],
    [fk(['snapshot_id','run_id'],'migration_runs',['snapshot_id','id'])])
support.append(run_contract)
origins = next(t for t in support if t['name']=='operational_origins')
origins['indexes'].append(index('INDEX',['snapshot_id','linked_by_run_id'],'Import run must belong to origin snapshot'))
origins['foreign_keys'].append(fk(['snapshot_id','linked_by_run_id'],'migration_runs',['snapshot_id','id']))
checkpoint = next(t for t in support if t['name']=='migration_checkpoints')
checkpoint['columns'] += [column('snapshot_id','BIGINT UNSIGNED NOT NULL'), column('mapping_sha256','BINARY(32) NOT NULL'),
    column('batch_ordinal','BIGINT UNSIGNED NOT NULL DEFAULT 0'), column('batch_count','BIGINT UNSIGNED NOT NULL DEFAULT 0'),
    column('lease_owner','BINARY(16) NULL DEFAULT NULL'), column('fencing_token','BIGINT UNSIGNED NOT NULL DEFAULT 0'),
    column('lease_expires_at','DATETIME(6) NULL DEFAULT NULL')]
checkpoint['indexes'] += [index('UNIQUE',['snapshot_id','mapping_sha256','source_table_id','phase'],'One leased immutable snapshot/mapping lane across run retries'),
    index('INDEX',['run_id','snapshot_id','mapping_sha256'],'Exact run interpretation FK')]
checkpoint['foreign_keys'].append(fk(['run_id','snapshot_id','mapping_sha256'],'migration_run_contracts',['run_id','snapshot_id','mapping_sha256']))
checkpoint['checks'].append('(lease_owner IS NULL AND lease_expires_at IS NULL) OR (lease_owner IS NOT NULL AND lease_expires_at IS NOT NULL)')

# Stable graph information; all raw rows load before supplemental relationship resolution.
graph = {t['name']: set() for t in tables}
for r in relationships:
    if r['target_table'] in graph and r['parent_target_table'] in graph:
        graph[r['target_table']].add(r['parent_target_table'])
pending, order = {n:set(v) for n,v in graph.items()}, []
while pending:
    ready = sorted(n for n,v in pending.items() if not v)
    if not ready:
        break
    order.extend(ready)
    for n in ready: del pending[n]
    for v in pending.values(): v.difference_update(ready)

schema = {'status':'PROPOSED P3.0 physical schema; explicit confirmation required before migrations',
          'source_manifest_sha256':digest(MANIFEST),
          'source_schema_sha256':hashlib.sha256(SOURCE.read_bytes().replace(b'\r\n', b'\n')).hexdigest(),
          'source_schema_hash_encoding':'UTF-8 metadata text with LF newlines; preservation manifest remains byte-for-byte',
          'rules':{'all_fk_actions':'RESTRICT/RESTRICT', 'historical_payload':'exact typed values; no normalization or rounding',
                   'legacy_column_defaults':'NULL only; original source defaults/nullability preserved as metadata and enforced on imported projection',
                   'presence':'Source metadata distinguishes absent column from SQL NULL; inapplicable variant columns stay NULL',
                   'new_writes':'Web entity may have zero origins and no fabricated legacy key; projects retain source-qualified allocated project number',
                   'snapshot_continuity':'First import establishes identity; later snapshot reconciliation requires explicit reviewed policy and decision locator; no ordinal continuity inference'},
          'tables':tables, 'support_tables':support, 'mappings':mappings, 'relationships':relationships,
          'existing_table_alterations':[{'table':'source_records','add_indexes':[index('UNIQUE',['source_id','table_id','snapshot_id','id'],'Origin FK proves exact occurrence snapshot/source/table')], 'data_changes':'none'},
              {'table':'migration_runs','add_indexes':[index('UNIQUE',['snapshot_id','id'],'Run/snapshot consistency in origins and frozen run contracts')], 'data_changes':'none'}],
          'archive_only_tables':sorted(archive), 'parent_first_order':order,
          'unresolved_cycle_nodes':sorted(pending),
          'historical_actor_fields':{'log':['Log_User'],'mevisit':['Created_By','Edited_By'], 'microproject_rehab':['Created_By','Edited_By'],'visit':['Created_By','Edited_By']},
          'explicit_unresolved_contracts':['user_notification.Pay_ID polymorphic target requires evidence; sign/body alone not authoritative',
              'notification table absent from supplied schemas', 'auxiliary application links not inferred from column names',
              'text actors never replaced by account IDs; reviewed resolution supplemental only'],
          'follow_on_schema':'FTP/BLOB manifest, reconciliation-run evidence and optional actor-resolution/polymorphic ledgers require separately enumerated physical addenda before their migrations; no implied schema approval'}
validation = {'source_tables':len(mappings),'distinct_names':len(names),
              'source_columns':sum(len(t['columns']) for t in mappings),
              'shared_business_tables':len(tables),'support_tables':len(support),
              'shared_source_instances':sum(t['target_table'] is not None for t in mappings),
              'preservation_only_source_instances':sum(t['target_table'] is None for t in mappings),
              'operational_column_mappings':sum(len(t['columns']) for t in mappings if t['target_table']),
              'preservation_only_column_mappings':sum(len(t['columns']) for t in mappings if not t['target_table']),
              'declared_relationship_occurrences':sum(len(t['source_foreign_keys']) for t in manifest['tables']),
              'grouped_relationship_contracts':len(relationships),
              'shared_relationship_contracts':sum(r['target_table'] is not None for r in relationships),
              'represented_declared_relationship_occurrences':sum(o['contract']!='application-only' for r in relationships for o in r['occurrences']),
              'physical_payload_columns':sum(sum(c['name'].startswith('c_') for c in t['columns']) for t in tables),
              'variant_fields':sum(1 for t in tables for c in {re.sub(r'__[0-9a-f]{8}$','',x['name']) for x in t['columns'] if re.search(r'__[0-9a-f]{8}$',x['name'])}),
              'unresolved_cycle_nodes':len(pending),
              'assertions':[]}
assert len(mappings)==616 and validation['source_columns']==3947
assert validation['represented_declared_relationship_occurrences']==validation['declared_relationship_occurrences']
for m in mappings:
    assert all(c['target_column'] is not None for c in m['columns']) if m['target_table'] else all(c['target_column'] is None for c in m['columns'])
for t in tables+support:
    assert len({c['name'].lower() for c in t['columns']})==len(t['columns'])
    assert len(t['columns'])<1017
    assert len(t['indexes'])<=64
    for k in t['indexes']:
        assert set(k['columns']) <= {c['name'] for c in t['columns']}
    for f in t['foreign_keys']:
        assert set(f['columns']) <= {c['name'] for c in t['columns']}
        parent = next((x for x in tables+support if x['name']==f['parent_table']),None)
        if parent:
            assert any(k['kind'] in ('PRIMARY','UNIQUE') and k['columns']==f['parent_columns'] for k in parent['indexes']),f
        assert any(k['columns'][:len(f['columns'])]==f['columns'] for k in t['indexes']), f
validation['assertions']=['616/616 table and 3947/3947 column mappings', 'no identifier collision',
                          'all local FKs reference exact unique keys', 'all child FK indexes present',
                          'MySQL 1017-column/64-index limits respected', 'all original declared relationship instances retained']
validation['max_table_columns'] = max(len(t['columns']) for t in tables+support)
validation['max_table_indexes'] = max(len(t['indexes']) for t in tables+support)
# All planned index columns are fixed-width numeric/ASCII/binary technical keys;
# even the largest checkpoint key is 8+32+8+24 = 72 bytes, below MySQL's 3072.
validation['max_planned_index_bytes_upper_bound'] = 96
for filename, value in [('operational-schema.json',schema),('schema-coverage.json',validation)]:
    (ROOT/filename).write_text(json.dumps(value,ensure_ascii=False,indent=2)+'\n',encoding='utf-8',newline='\n')

lines = ['# P3.0 shared operational physical breakdown', '', schema['status']+'.', '',
         'Generated by build-operational-schema.py from schema metadata; no business rows. Read schema-proposal.md for identity, constraint, preservation and approval boundaries.', '',
         'All tables: InnoDB; default utf8mb4_bin. Every FK is RESTRICT on update/delete. Each field definition below includes nullability/default. No implicit timestamps or implicit defaults.', '']
for t in support+tables:
    lines += ['## '+t['name'],'','| Column | Exact definition |','|---|---|']
    lines += ['| `'+c['name']+'` | `'+c['definition']+'` |' for c in t['columns']]
    lines += ['', 'Keys / indexes:'] + ['- '+k['kind']+' `('+','.join(k['columns'])+')`: '+k['reason']+'.' for k in t['indexes']]
    lines += ['', 'Foreign keys:'] + ['- `('+','.join(f['columns'])+')` → `'+f['parent_table']+'('+','.join(f['parent_columns'])+')`; RESTRICT update/delete.' for f in t['foreign_keys']]
    lines += ['', 'Checks:'] + ['- `'+c+'`.' for c in t['checks']]+['']
lines += ['## Existing-table alterations awaiting confirmation','','Add UNIQUE(source_id,table_id,snapshot_id,id) to source_records and UNIQUE(snapshot_id,id) to migration_runs; no data changes. These are required for exact snapshot-origin/run foreign keys.','']
(ROOT/'schema-physical.md').write_text('\n'.join(lines),encoding='utf-8',newline='\n')
print(json.dumps(validation,indent=2))
