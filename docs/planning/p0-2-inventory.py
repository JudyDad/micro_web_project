"""Read-only SQL/source inventory. Emits metadata/DDL only; never SQL rows or credentials."""
import json,re,hashlib,zipfile
from pathlib import Path
OUT=Path(__file__).parent
SRC=Path(r'D:\MyProjects\MicroProject newTest\MyWorkApplication')
BACKUPS=Path(r'D:\01-SqlBackupAndFTP backups\hcsyria_micro')
schemas=json.loads((OUT/'source-schemas.json').read_text())
audit=json.loads((OUT/'backup-audit.json').read_text())
quoted=re.compile(r"'(?:\\.|''|[^'\\])*'",re.S)
names=sorted(set().union(*(set(s) for s in schemas.values())))
assert len(names)==127
def classify(t):
    if t in {'dept','resultstable','temp_gender','visit_schedule_temp','work_exp','t1'}: return 'Uncertain/auxiliary; retain, investigate before disposition'
    if t in {'file','image','manualfile'}: return 'Attachment metadata/BLOB'
    if t in {'databases','version','user','role','log','user_notification'}: return 'Identity/configuration/audit/notification'
    if t.startswith('task'): return 'Staff task workflow'
    if t in {'loan','payment','purchasing','material','exefile'}: return 'Finance/execution transaction'
    if t.startswith('profession_meeting'): return 'Profession meeting workflow'
    if t.startswith(('person_','microproject_','mevisit_','visit_','checklist_')): return 'Relationship/detail workflow'
    if t in {'person','family','microproject','checklist','mevisit','visit','rehabvisit','office_monitoring','specialcase'}: return 'Core/workflow record'
    return 'Reference/lookup/questionnaire definition; retain source-qualified'
def shape(ddl):
    return re.sub(r'AUTO_INCREMENT=\d+','AUTO_INCREMENT=<counter>',ddl)
def metadata(ddl):
    cols={m[1]:m[2] for m in re.finditer(r'^  `([^`]+)` (.*?)(?:,)?$',ddl,re.M)}
    pk=re.search(r'PRIMARY KEY \(([^)]+)\)',ddl)
    return {'columns':cols,'primary_key':re.findall(r'`([^`]+)`',pk[1]) if pk else [],'unique_keys':re.findall(r'UNIQUE KEY `([^`]+)` \(([^)]+)\)',ddl),'foreign_keys':re.findall(r'FOREIGN KEY \(([^)]+)\) REFERENCES `([^`]+)` \(([^)]+)\)([^\n]*)',ddl)}
result={'method':'Static parsing only. Restored counts: NONE. No SQL execution, network, source mutation, or raw row output.','tables':{},'sources':[],'routines':[]}
for t in names:
    citydata={c:metadata(s[t]) for c,s in schemas.items() if t in s}
    result['tables'][t]={'classification':classify(t),'disposition':'Retain all source rows and original values; source-qualified mapping required; target design pending P1.','cities':citydata,'ddl_variants_excluding_sequence_counter':len({shape(s[t]) for s in schemas.values() if t in s})}
for db in audit['databases']:
    path=BACKUPS/db['archive']
    with zipfile.ZipFile(path) as z:
        assert z.testzip() is None
        entries=[n for n in z.namelist() if n.lower().endswith('.sql')]
        assert len(entries)==1
        raw=z.read(entries[0])
    assert hashlib.sha256(raw).hexdigest()==db['sql_sha256']
    assert hashlib.sha256(path.read_bytes()).hexdigest()==db['archive_sha256']
    sql=raw.decode('utf-8-sig'); city=db['city']
    counts={t:0 for t in schemas[city]}
    for m in re.finditer(r'^INSERT INTO `([^`]+)`[^\r\n]*',sql,re.M):
        if m[1] in counts and 'VALUES' in m[0]:
            vals=quoted.sub("''",m[0].split('VALUES',1)[1])
            counts[m[1]]+=len(re.findall(r'(?:^|,)\s*\(',vals))
    assert counts['microproject']==db['project_count']
    for t in counts:
        result['tables'][t]['cities'][city]['parsed_insert_tuple_count']=counts[t]
        m=re.search(r'CREATE TABLE (?:IF NOT EXISTS )?`'+re.escape(t)+r'`',sql)
        assert m
        result['tables'][t]['cities'][city]['evidence']=db['archive']+'!'+entries[0]+':'+str(sql.count('\n',0,m.start())+1)
    starts=list(re.finditer(r'CREATE[^\n]*?\b(PROCEDURE|FUNCTION|TRIGGER|EVENT)\s+`?([\w]+)`?',sql,re.I))
    for m in starts:
        end=sql.find('END ;;',m.end())
        if end<0: end=sql.find('END$$',m.end())
        if end<0: end=sql.find('\nDELIMITER',m.end())
        assert end>=0
        body=sql[m.start():end]
        result['routines'].append({'city':city,'kind':m[1].upper(),'name':m[2],'line':sql.count('\n',0,m.start())+1,'source':db['archive']+'!'+entries[0],'definition_sha256':hashlib.sha256(body.encode()).hexdigest(),'referenced_table_names':[t for t in names if re.search(r'(?<![\w])`?'+re.escape(t)+r'`?(?![\w])',body,re.I)],'statement_verbs':sorted(set(re.findall(r'\b(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|TRUNCATE|CALL)\b',body,re.I)))})
    assert sum(x['city']==city and x['kind']=='PROCEDURE' for x in result['routines'])==db['procedure_count']
    result['sources'].append({'city':city,'tables':len(counts),'parsed_projects':counts['microproject'],'parsed_total_insert_tuples':sum(counts.values()),'sha256_verified':True,'crc_verified':True})
# DDL-only differences retain exact per-city columns, keys and FK semantics above.
for t,rec in result['tables'].items():
    allcols=set().union(*(set(v['columns']) for v in rec['cities'].values()))
    rec['differing_columns']={col:{c:v['columns'].get(col,'<absent>') for c,v in rec['cities'].items()} for col in sorted(allcols) if len({v['columns'].get(col,'<absent>') for v in rec['cities'].values()})>1}
    rec['undeclared_link_candidates']={c:[col for col in v['columns'] if (re.search(r'(^|_)id$|created_by|edited_by|log_user',col,re.I)) and (col not in v['primary_key'] or len(v['primary_key'])>1) and not any('`'+col+'`' in fk[0] for fk in v['foreign_keys'])] for c,v in rec['cities'].items()}
local_sql=SRC/'Procedures'/'all_procedures.sql'
local_text=local_sql.read_text(encoding='utf-8-sig')
result['source_file_routines']=[{'name':m[1],'evidence':'Procedures/all_procedures.sql:'+str(local_text.count('\n',0,m.start())+1)} for m in re.finditer(r'CREATE\s+PROCEDURE\s+`([^`]+)`',local_text,re.I)]
assert len(result['source_file_routines'])==8
(OUT/'p0-2-data-inventory.json').write_text(json.dumps(result,indent=2),encoding='utf-8')
lines=['# P0.2 source table register','','Generated by `p0-2-inventory.py`. All counts are parsed INSERT tuples, not restored rows. Keys and every per-city column/FK definition are in `p0-2-data-inventory.json`; original full DDL is in `source-schemas.json`. No table is approved for discard.','','| Table | Classification | Present cities | PK variants | DDL variants (ignore sequence) | Parsed tuples by city |','|---|---|---|---|---:|---|']
for t,r in result['tables'].items():
    keys=sorted({','.join(v['primary_key']) or 'NONE — snapshot ordinal required' for v in r['cities'].values()})
    lines.append('| '+ ' | '.join([t,r['classification'],', '.join(r['cities']),'; '.join(keys),str(r['ddl_variants_excluding_sequence_counter']),'; '.join(c+': '+str(v['parsed_insert_tuple_count']) for c,v in r['cities'].items())])+' |')
lines+=['','## Routine register','','Names are database-local; identical names are not assumed identical definitions. Hashes and referenced tables are recorded per city. SQL was not executed.','','| City | Kind/name | Source evidence | Referenced tables |','|---|---|---|---|']
for r in result['routines']: lines.append('| '+r['city']+' | '+r['kind']+' '+r['name']+' | '+r['source']+':'+str(r['line'])+' | '+', '.join(r['referenced_table_names'])+' |')
(OUT/'p0-2-table-register.md').write_text('\n'.join(lines)+'\n',encoding='utf-8')
print(json.dumps({'tables':len(names),'routine_definitions':len(result['routines']),'sources':result['sources']}))
