"""Read immutable ZIP SQL; emit aggregate whole-table equality evidence only.

No SQL execution, credentials, business values or individual-row hashes are emitted.
Numeric literal spelling is compared exactly; no numeric/text cleanup is performed.
"""
import collections
import hashlib
import itertools
import json
import re
import struct
import time
import zipfile
from pathlib import Path

HERE = Path(__file__).resolve().parent
PLAN = HERE.parent
BACKUPS = Path(r'D:\01-SqlBackupAndFTP backups\hcsyria_micro')
AUDIT = json.loads((PLAN / 'backup-audit.json').read_text(encoding='utf-8'))
MANIFEST = json.loads((PLAN.parent.parent / 'database/schema/preservation-v1.json').read_text(encoding='utf-8'))
INVENTORY = json.loads((PLAN / 'p0-2-data-inventory.json').read_text(encoding='utf-8'))
META = {(t['city'], t['source_table']): t for t in MANIFEST['tables']}
HEADER = re.compile(rb'^INSERT INTO `([^`]+)`\s*(\([^;]*?\))?\s*VALUES\s*', re.M)
TOKEN = re.compile(rb"\s*(?:(?P<string>'(?:\\.|''|[^'\\])*')|(?P<hex>0x[0-9a-fA-F]+)|(?P<null>NULL\b)|(?P<number>[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?)|(?P<punct>[(),;]))", re.S)
ESCAPE = re.compile(rb"\\(.)|''", re.S)

def sha(value):
    return hashlib.sha256(value).digest()

def frame(values):
    return b''.join(struct.pack('>Q', len(v))+v for v in values)

def literal(match):
    if match.lastgroup == 'null':
        return b'Z'
    if match.lastgroup == 'number':
        return b'N'+match.group('number')
    if match.lastgroup == 'hex':
        # Keep SQL literal kinds distinct: 0x31 and '1' can store different
        # values in a numeric column even though their decoded bytes match.
        return b'H'+bytes.fromhex(match.group('hex')[2:].decode('ascii'))
    if match.lastgroup == 'string':
        escapes = {b'0':b'\0', b'b':b'\b', b'n':b'\n', b'r':b'\r', b't':b'\t', b'Z':b'\x1a', b'%':b'\\%', b'_':b'\\_'}
        return b'S'+ESCAPE.sub(lambda m: b"'" if m.group(0)==b"''" else escapes.get(m.group(1),m.group(1)), match.group('string')[1:-1])
    raise ValueError('Unsupported literal kind')

def type_contract(column, ddl):
    value = re.split(r'\s+(?:NOT NULL|DEFAULT|AUTO_INCREMENT)\b', column['source_definition'], 1)[0]
    value = re.sub(r'\s+NULL$', '', value)
    if re.match(r'(?:var)?char\(|(?:tiny|medium|long)?text\b|enum\(', value):
        coll = re.search(r'COLLATE\s+(\w+)',value)
        char = re.search(r'CHARACTER SET\s+(\w+)',value)
        charset = char.group(1) if char else coll.group(1).split('_')[0] if coll else re.search(r'DEFAULT CHARSET=(\w+)',ddl).group(1)
        if not char:
            value = re.sub(r'\s+COLLATE',' CHARACTER SET '+charset+' COLLATE',value) if coll else value+' CHARACTER SET '+charset
        if not coll:
            found = re.search(r'COLLATE=(\w+)',ddl)
            value += ' COLLATE '+(found.group(1) if found else charset+'_general_ci')
    return (column['source_column'],value,'NOT NULL' in column['source_definition'])

def read_city(entry):
    city = entry['city']
    path = BACKUPS / entry['archive']
    if hashlib.sha256(path.read_bytes()).hexdigest()!=entry['archive_sha256']:
        raise ValueError('Archive digest changed: '+city)
    with zipfile.ZipFile(path) as archive:
        members=[m for m in archive.namelist() if m.endswith('.sql')]
        if len(members)!=1:
            raise ValueError('Expected one SQL member')
        raw=archive.read(members[0])
    if hashlib.sha256(raw).hexdigest()!=entry['sql_sha256']:
        raise ValueError('SQL digest changed: '+city)
    data={}
    for (c,name),meta in META.items():
        if c!=city: continue
        columns=[x['source_column'] for x in meta['columns']]
        contract=[type_contract(x,meta['original_ddl']) for x in meta['columns']]
        data[name]={'columns':columns,'schema':sha(json.dumps(contract,ensure_ascii=False).encode()),
                    'rows':collections.Counter(),'keys':{},'count':0,'pk':meta['legacy_primary_key']}
    for header in HEADER.finditer(raw):
        name=header.group(1).decode('utf-8')
        if name not in data: raise ValueError('Unknown INSERT table')
        table=data[name]
        columns=[x.decode('utf-8') for x in re.findall(rb'`([^`]+)`', header.group(2))] if header.group(2) else table['columns']
        if len(columns)!=len(set(columns)) or set(columns)!=set(table['columns']):
            raise ValueError('Unexpected INSERT columns: '+city+'.'+name)
        projection=[columns.index(x) for x in table['columns']]
        pk=[table['columns'].index(x) for x in table['pk']]
        pos=header.end(); row=None; expect_value=False; had_value=False
        while True:
            token=TOKEN.match(raw,pos)
            if not token: raise ValueError('Unsupported INSERT syntax at '+city+'.'+name+' byte '+str(pos))
            pos=token.end()
            if token.lastgroup=='punct':
                punct=token.group('punct')
                if punct==b';':
                    if row is not None: raise ValueError('Unclosed tuple')
                    break
                if punct==b'(':
                    if row is not None: raise ValueError('Nested expression')
                    row=[]; expect_value=True; had_value=False
                elif punct==b')':
                    if row is None or expect_value or len(row)!=len(columns): raise ValueError('Invalid tuple width')
                    ordered=[row[i] for i in projection]
                    row_digest=sha(frame(ordered))
                    table['rows'][row_digest]+=1
                    table['count']+=1
                    if pk:
                        key_digest=sha(frame([ordered[i] for i in pk]))
                        if key_digest in table['keys']: raise ValueError('Duplicate source PK in frozen dump')
                        table['keys'][key_digest]=row_digest
                    row=None; had_value=True
                elif row is not None:
                    if expect_value: raise ValueError('Unexpected tuple separator')
                    expect_value=True
            else:
                if row is None or not expect_value: raise ValueError('Unexpected literal')
                row.append(literal(token)); expect_value=False
    for name,table in data.items():
        expected=INVENTORY['tables'][name]['cities'][city]['parsed_insert_tuple_count']
        if table['count']!=expected:
            raise ValueError('Count mismatch: '+city+'.'+name)
        table['signature']=sha(table['schema']+frame([key+struct.pack('>Q',count) for key,count in sorted(table['rows'].items())]))
    return data

def run():
    start=time.monotonic()
    cities={}
    for entry in AUDIT['databases']:
        cities[entry['city']]=read_city(entry)
        print('Verified '+entry['city']+'; tables='+str(len(cities[entry['city']]))+'; rows='+str(sum(t['count'] for t in cities[entry['city']].values())),flush=True)
    names=sorted(INVENTORY['tables'])
    parents={name:set() for name in names}
    for t in MANIFEST['tables']:
        parents[t['source_table']].update(r['parent_table'] for r in t['source_foreign_keys'])
    def closure(name):
        seen=set(); todo=list(parents[name])
        while todo:
            parent=todo.pop()
            if parent in seen: continue
            seen.add(parent); todo.extend(parents[parent]-seen)
        return seen
    def equal(name,a,b):
        return name in cities[a] and name in cities[b] and cities[a][name]['signature']==cities[b][name]['signature']
    groups=[]; empty=[]; rows=[]; conflicts={}
    for name in names:
        present=[c for c in cities if name in cities[c]]
        parts=collections.defaultdict(list)
        for c in present: parts[cities[c][name]['signature']].append(c)
        for members in parts.values():
            if len(members)<2: continue
            count=cities[members[0]][name]['count']
            unmatched=sorted(p for p in closure(name) if any(not equal(p,a,b) for a,b in itertools.combinations(members,2)))
            record={'table':name,'cities':members,'rows_per_city':count,'saved_duplicate_occurrences':count*(len(members)-1),
                    'full_table_sha256':cities[members[0]][name]['signature'].hex(),
                    'schema_contract_sha256':cities[members[0]][name]['schema'].hex(),
                    'declared_parent_closure':sorted(closure(name)),'unmatched_parents':unmatched,
                    'dependency_closed':not unmatched,'classification':INVENTORY['tables'][name]['classification']}
            (groups if count else empty).append(record)
        pairs=[]
        for a,b in itertools.combinations(present,2):
            left,right=cities[a][name],cities[b][name]
            common=set(left['keys'])&set(right['keys'])
            same=sum(left['keys'][k]==right['keys'][k] for k in common)
            pairs.append({'cities':[a,b],'same_id_exact_payload':same,'same_id_conflicting_payload':len(common)-same,
                          'column_contract_equal':left['schema']==right['schema'],'whole_table_equal':equal(name,a,b)})
        conflicts[name]=pairs
        rows.append({'table':name,'cities':present,'counts':{c:cities[c][name]['count'] for c in present},
                     'nonempty_equal_groups':sum(g['table']==name for g in groups),'empty_equal_groups':sum(g['table']==name for g in empty),
                     'max_same_id_conflicts':max((p['same_id_conflicting_payload'] for p in pairs),default=0)})
    summary={'method':'Exact decoded SQL-string/binary payload and numeric literal spelling; ordered source column/storage/nullability contract; multiset tuple hashes retain duplicates; schema syntax-only charset inference; no data normalization or SQL execution',
             'source_instances':len(META),'source_columns':sum(len(t['columns']) for t in META.values()),
             'rows_verified':sum(t['count'] for c in cities.values() for t in c.values()),
             'distinct_names':len(names),'nonempty_equal_groups':groups,'empty_equal_groups':empty,
             'table_coverage':rows,'city_pair_aggregate_conflicts':conflicts,
             'elapsed_seconds':round(time.monotonic()-start,2)}
    lines=['# Whole-table shared-data candidate audit','','Read-only frozen SQL audit, 17 October 2026. **Candidates only; no rows, schemas or approved mappings changed.** User authorized whole identical tables only. Matching subsets in differing tables are not candidates.','',
           'All five ZIP and SQL SHA-256 digests matched backup-audit.json. Every parsed table count matched its frozen inventory baseline. No raw rows, values, row IDs or individual-row hashes are emitted.','',
           'Method: '+summary['method']+'. Cryptographic digests are comparison accelerators, not permission to merge on ID alone. SHA-256 collision resistance is assumed. Original numeric token spelling is strict; semantically equal numbers with different dump spellings may be conservative false negatives. Table order and row order may differ; ordered column contracts and duplicate multiplicities remain significant. Source defaults/autoincrement counters remain provenance and future-write decisions, not payload equality.','',
           'Verified **'+str(summary['source_instances'])+' table instances / '+str(summary['distinct_names'])+' names / '+str(summary['source_columns'])+' columns / '+str(summary['rows_verified'])+' tuple occurrences**.','',
           '## Nonempty whole-table equality groups','','| Table | Equal cities | Rows each | Duplicate occurrences | Declared parent closure passes | Unmatched parents |','|---|---|---:|---:|---|---|']
    for g in groups:
        lines.append('| '+g['table']+' | '+', '.join(g['cities'])+' | '+str(g['rows_per_city'])+' | '+str(g['saved_duplicate_occurrences'])+' | '+('yes' if g['dependency_closed'] else 'no')+' | '+(', '.join(g['unmatched_parents']) or 'none')+' |')
    lines+=['','Declared-parent closure is conservative and requires full exact parent equality throughout each group. It does not certify undeclared application relationships, mutable-write semantics, credentials, tenant visibility or permission equivalence. Historical actors and tasks require separate membership/authorization handling. These groups never authorize merging projects, people or accounts by coincidentally matching IDs.','',
            '## Identical empty-table groups','','| Table | Equal cities |','|---|---|']
    lines+=['| '+g['table']+' | '+', '.join(g['cities'])+' |' for g in empty]
    lines+=['','Empty equality is not evidence of shared future business scope; there are no existing rows to consolidate.','',
            '## Requested category, subcategory and task definitions','','| Table | City counts | Whole equal groups | Max same-ID conflicts between city pairs |','|---|---|---:|---:|']
    requested=['category','subcategory','task','task_microproject','task_user']
    for row in rows:
        if row['table'] in requested:
            lines.append('| '+row['table']+' | '+', '.join(c+': '+str(n) for c,n in row['counts'].items())+' | '+str(row['nonempty_equal_groups'])+' | '+str(row['max_same_id_conflicts'])+' |')
    lines+=['','## All-table disposition coverage','','| Table | Counts by city | Nonempty equal groups | Empty equal groups | Max same-ID conflicts |','|---|---|---:|---:|---:|']
    lines+=['| '+r['table']+' | '+', '.join(c+': '+str(n) for c,n in r['counts'].items())+' | '+str(r['nonempty_equal_groups'])+' | '+str(r['empty_equal_groups'])+' | '+str(r['max_same_id_conflicts'])+' |' for r in rows]
    lines+=['','## Aggregate machine-readable evidence','','The JSON below contains table names and aggregate counts only. Same-ID diagnostics never authorize sharing differing tables.','', '```json',json.dumps(summary,ensure_ascii=False,indent=2),'```','',
            'Physical approach proposed for review: retain every immutable per-source occurrence and introduce one shared dictionary row per approved complete-table equality group, plus explicit source/city membership and original-key-to-shared-row mappings. Canonical child links may target shared lookup identity only when their whole parent closure is compatible. Mutable tasks retain city membership and per-city capability checks; shared content must not imply shared completion/assignment state. No global uniqueness on legacy IDs, no sharing of arbitrary matching subsets, and no loss of original source/snapshot provenance.','',
            'Reproduce with Python 3: run audit-shared-data.py. It reads only the five frozen archives and committed schema metadata, validates every row count and digest, and overwrites only this aggregate report. Elapsed seconds: '+str(summary['elapsed_seconds'])+'.','']
    (HERE/'shared-data-candidate-audit.md').write_text('\n'.join(lines),encoding='utf-8',newline='\n')
    print(json.dumps({'nonempty_equal_groups':len(groups),'dependency_closed_nonempty_groups':sum(g['dependency_closed'] for g in groups),'empty_equal_groups':len(empty),'rows':summary['rows_verified'],'seconds':summary['elapsed_seconds'],'requested':[g for g in groups if g['table'] in requested]},indent=2),flush=True)

if __name__=='__main__':
    run()
