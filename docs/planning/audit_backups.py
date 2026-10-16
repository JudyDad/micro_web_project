"""Read supplied ZIP dumps; write schema/aggregate evidence only. Never execute SQL."""
import collections
import hashlib
import itertools
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(r'D:\01-SqlBackupAndFTP backups\hcsyria_micro')
OUT = Path(__file__).parent
FILES = {
    'hcsyria_micro202610161513.zip': 'Aleppo',
    'hcsyria_micro_d202610161515.zip': 'Damascus',
    'hcsyria_micro_h202610161517.zip': 'Homs',
    'hcsyria_micro_l202610161520.zip': 'Latakia',
    'hcsyria_micro_w202610161522.zip': 'AlWadi',
}
QUOTED = re.compile(r"'(?:\\.|''|[^'\\])*'", re.S)
result = {'method': 'Static SQL dump inspection; not a restore or complete integrity certification.', 'databases': [], 'overlaps': []}
sets = {}
schema = {}
for filename, city in FILES.items():
    path = ROOT / filename
    with zipfile.ZipFile(path) as z:
        bad = z.testzip()
        if bad:
            raise ValueError(f'Archive CRC failure: {filename}')
        raw = z.read(z.namelist()[0])
    sql = raw.decode('utf-8-sig')
    ddl = {m[1]: m[0].replace('\r', '') for m in re.finditer(
        r'CREATE TABLE (?:IF NOT EXISTS )?`([^`]+)`\s*\((.*?)\) ENGINE[^;]*;', sql, re.S)}
    schema[city] = ddl
    ids = []
    for m in re.finditer(r'^INSERT INTO `microproject`[^\r\n]*', sql, re.M):
        header, values = m[0].split('VALUES', 1)
        assert re.search(r'\(\s*`MP_ID`\s*,', header), 'Unexpected column order'
        safe = QUOTED.sub("''", values)
        ids.extend(map(int, re.findall(r'(?:^|,)\s*\((-?\d+)\s*,', safe)))
    assert ids and len(ids) == len(set(ids)), 'Missing or duplicate parsed project IDs'
    sets[city] = set(ids)
    result['databases'].append({
        'city': city, 'archive': filename,
        'archive_sha256': hashlib.sha256(path.read_bytes()).hexdigest(),
        'sql_sha256': hashlib.sha256(raw).hexdigest(), 'zip_crc_ok': True,
        'table_count': len(ddl), 'project_count': len(ids),
        'min_project_id': min(ids), 'max_project_id': max(ids),
        'project_fk_tables': [t for t, d in ddl.items() if 'REFERENCES `microproject`' in d],
        'procedure_count': len(re.findall(r'CREATE[^\n]*PROCEDURE', sql)),
    })
for a, b in itertools.combinations(sets, 2):
    common = sets[a] & sets[b]
    result['overlaps'].append({'city_a': a, 'city_b': b, 'shared_project_numbers': len(common), 'examples': sorted(common)[:5]})
union = set().union(*(set(d) for d in schema.values()))
result['distinct_table_names'] = len(union)
result['total_projects'] = sum(len(s) for s in sets.values())
result['tables_absent_by_city'] = {city: sorted(union - set(d)) for city, d in schema.items()}
(OUT / 'backup-audit.json').write_text(json.dumps(result, indent=2), encoding='utf-8')
(OUT / 'source-schemas.json').write_text(json.dumps(schema, indent=2), encoding='utf-8')
print(json.dumps({'total_projects': result['total_projects'], 'distinct_table_names': len(union), 'artifacts': ['backup-audit.json', 'source-schemas.json']}))
