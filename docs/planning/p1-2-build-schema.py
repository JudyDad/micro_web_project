"""Metadata-only proposed physical schema; does not execute SQL or read business rows."""
import json, re, hashlib
from pathlib import Path

ROOT = Path(__file__).resolve().parent
schemas = json.loads((ROOT / 'source-schemas.json').read_text(encoding='utf-8'))
inventory = json.loads((ROOT / 'p0-2-data-inventory.json').read_text(encoding='utf-8'))
tables, edges, md = [], [], ['# P1.2 exhaustive typed retention table breakdown', '',
    'DRAFT — schema confirmation and restored profiling pending. Not executable DDL.', '',
    'Read p1-2-target-physical-schema.md for the common columns, indexes, constraint policy and limits. Every table below is retained in the same proposed operational MySQL database as a source-specific compatibility table. No table/column is discarded. This appendix is not approval of a normalized application schema.', '']
for city, source_tables in schemas.items():
    for name, ddl in source_tables.items():
        meta = inventory['tables'][name]['cities'][city]
        target = 'legacy_' + city.lower() + '_' + name
        assert len(target) <= 64
        assert '__record_id' not in meta['columns']
        columns = []
        for col, definition in meta['columns'].items():
            columns.append({'source_column': col, 'target_column': col,
                'source_definition': definition,
                'target_definition': definition.replace(' AUTO_INCREMENT', ''),
                'transformation': 'identity; auto-increment attribute removed, original numeric value retained'})
        indexes = [line.strip().rstrip(',') for line in ddl.splitlines()
                   if re.match(r'\s*(?:PRIMARY KEY|UNIQUE KEY|KEY|FULLTEXT KEY|SPATIAL KEY)', line)]
        relationship_ids = []
        for number, fk in enumerate(meta['foreign_keys'], 1):
            child, parent, parent_cols, actions = fk
            edge_id = f'{city}.{name}.fk{number}'
            edge = {'id': edge_id, 'kind': 'declared_fk', 'source_city': city,
                'child_source_table': name, 'child_columns': re.findall(r'`([^`]+)`', child),
                'parent_source_table': parent, 'parent_columns': re.findall(r'`([^`]+)`', parent_cols),
                'child_target_table': target, 'parent_target_table': 'legacy_'+city.lower()+'_'+parent,
                'legacy_actions': actions.strip(),
                'target_rule': 'source-qualified relationship ledger; RESTRICT mapped-record deletes; raw tuple unchanged; orphan/ambiguous retained as exception'}
            edges.append(edge); relationship_ids.append(edge_id)
        entry = {'source_city': city, 'source_table': name, 'target_table': target,
            'classification': inventory['tables'][name]['classification'],
            'disposition': 'retain all columns and every row in typed compatibility table plus immutable raw archive',
            'columns': columns, 'source_primary_key': meta['primary_key'],
            'source_unique_keys': meta['unique_keys'], 'source_index_definitions': indexes,
            'source_table_options': ddl.split(') ENGINE=')[-1],
            'source_ddl_sha256': hashlib.sha256(ddl.encode()).hexdigest(),
            'relationship_ids': relationship_ids,
            'undeclared_link_candidates': inventory['tables'][name]['undeclared_link_candidates'].get(city, []),
            'source_sequence_hint': re.search(r'AUTO_INCREMENT=(\d+)', ddl).group(1) if re.search(r'AUTO_INCREMENT=(\d+)', ddl) else None,
            'identity': 'ordered typed primary-key tuple' if meta['primary_key'] else 'immutable snapshot/table/row ordinal; preserve identical duplicates',
            'evidence': meta['evidence']}
        tables.append(entry)
        md += [f'## {city}.{name} → {target}', '', entry['classification'], '',
               'Required common column: `__record_id BIGINT UNSIGNED NOT NULL`, primary key and RESTRICT FK to `migration_records.id`.', '',
               '| Original and target column | Target definition |', '|---|---|']
        md += [f"| `{c['source_column']}` | `{c['target_definition']}` |" for c in columns]
        md += ['', 'Source PK components: '+(', '.join('`'+x+'`' for x in meta['primary_key']) or 'none (ordinal identity required)')+'.',
               'Source indexes: '+('; '.join('`'+x+'`' for x in indexes) or 'none')+'.',
               'Table charset/engine evidence: `'+entry['source_table_options']+'`.',
               'Relationship mappings: '+(', '.join(relationship_ids) or 'none declared')+'.', '']

nodes = {t['target_table'] for t in tables}
assert all(e['parent_target_table'] in nodes for e in edges)
# Strongly-connected components expose cycles without claiming an acyclic load order.
graph = {n: [] for n in nodes}
for e in edges: graph[e['child_target_table']].append(e['parent_target_table'])
index, stack, onstack, ix, low, components = 0, [], set(), {}, {}, []
def visit(v):
    global index
    ix[v] = low[v] = index; index += 1; stack.append(v); onstack.add(v)
    for w in graph[v]:
        if w not in ix: visit(w); low[v] = min(low[v], low[w])
        elif w in onstack: low[v] = min(low[v], ix[w])
    if low[v] == ix[v]:
        comp = []
        while True:
            w = stack.pop(); onstack.remove(w); comp.append(w)
            if w == v: break
        components.append(sorted(comp))
for node in sorted(nodes):
    if node not in ix: visit(node)
cycles = [c for c in components if len(c)>1 or c[0] in graph[c[0]]]
result = {'status': 'proposed; not accepted; no migrations', 'tables': tables,
          'declared_relationships': edges, 'dependency_cycles': cycles,
          'load_order': ['source catalog and snapshot', 'all migration_records identities and raw archive',
                         'typed compatibility rows', 'resolved relationship ledger and exceptions',
                         'reconciliation and future operational projections only after approval']}
report = {'method': 'static metadata coverage only; no restore/import executed',
          'distinct_source_tables': len({t['source_table'] for t in tables}),
          'source_table_instances': len(tables), 'mapped_source_columns': sum(len(t['columns']) for t in tables),
          'declared_relationships': len(edges), 'keyless_instances': sum(not t['source_primary_key'] for t in tables),
          'composite_pk_instances': sum(len(t['source_primary_key'])>1 for t in tables),
          'cycle_components': len(cycles),
          'input_sha256': {f: hashlib.sha256((ROOT/f).read_bytes()).hexdigest() for f in ['source-schemas.json','p0-2-data-inventory.json']}}
assert report['distinct_source_tables'] == 127 and report['source_table_instances'] == 616
for city, st in schemas.items():
    assert set(st) == {t['source_table'] for t in tables if t['source_city']==city}
for t in tables:
    expected = re.findall(r'^\s*`([^`]+)`\s+', schemas[t['source_city']][t['source_table']], re.M)
    assert expected == [c['source_column'] for c in t['columns']]
for name, data in [('p1-2-target-schema.json', result), ('p1-2-coverage.json', report)]:
    (ROOT/name).write_text(json.dumps(data, ensure_ascii=False, indent=2)+'\n', encoding='utf-8')
(ROOT/'p1-2-table-breakdown.md').write_text('\n'.join(md)+'\n', encoding='utf-8')
print(json.dumps(report, indent=2))
