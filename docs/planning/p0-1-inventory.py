"""Read-only lexical inventory. Outputs identifiers and source locations only, never literals."""
from pathlib import Path
import re,json,hashlib,xml.etree.ElementTree as ET
ROOT=Path(r'D:\MyProjects\MicroProject newTest')
OUT=Path(__file__).parent
proj=ROOT/'MyWorkApplication/MyWorkApplication.csproj'
ns={'m':'http://schemas.microsoft.com/developer/msbuild/2003'}
files=[proj.parent/x.attrib['Include'].replace('\\','/') for x in ET.parse(proj).findall('.//m:Compile',ns)]
# Preserve offset/newline for evidence; strings are masked too, avoiding secret/PII extraction.
lex=re.compile(r'@"(?:""|[^"])*"|"(?:\\.|[^"\\])*"|\'(?:\\.|[^\'\\])*\'|//[^\n]*|/\*[\s\S]*?\*/')
def mask(s): return lex.sub(lambda m: ''.join('\n' if c=='\n' else ' ' for c in m[0]),s)
def loc(p,s,pos): return f'{p.relative_to(ROOT).as_posix()}:{s.count(chr(10),0,pos)+1}'
texts={p:p.read_text(encoding='utf-8-sig',errors='replace') for p in files if p.exists()}
clean={p:mask(s) for p,s in texts.items()}
forms={}
for p,s in clean.items():
 for m in re.finditer(r'class\s+(\w+)\s*:\s*(?:System.Windows.Forms.)?Form\b',s):
  forms[m[1]]={'name':m[1],'definition':loc(p,s,m.start()),'incoming':[],'outgoing':[],'actions':[]}
edges=[]; actions=[]; permissions=[]; events=[]
method=re.compile(r'\b(?:private|public|protected|internal)\s+(?:(?:static|async|override|virtual)\s+)*(?:[\w<>\[\],.?]+\s+)(\w+)\s*\([^;{}]*\)\s*\{')
for p,s in clean.items():
 owner=re.search(r'class\s+(\w+)',s)
 owner=owner[1] if owner else p.stem.split('.')[0]
 methods=list(method.finditer(s))
 for m in re.finditer(r'\bnew\s+(\w+)\s*\(',s):
  if m[1] not in forms: continue
  previous=[x for x in methods if x.start()<m.start()]
  row={'caller':owner,'method':previous[-1][1] if previous else '(initializer/constructor)','target':m[1],'evidence':loc(p,s,m.start())}
  edges.append(row); forms[m[1]]['incoming'].append(row)
  if owner in forms: forms[owner]['outgoing'].append(row)
 for m in methods:
  if re.search(r'Export|Print|Save|Delete|Insert|Update|Validat|Calculat|Check_User|Check.*Role',m[1],re.I) and not re.search('Mouse|Paint|Color|Image|Width|Height',m[1],re.I):
   row={'owner':owner,'method':m[1],'evidence':loc(p,s,m.start())}
   actions.append(row)
   if owner in forms: forms[owner]['actions'].append(row)
 for m in re.finditer(r'Program\.role\b',s): permissions.append({'owner':owner,'evidence':loc(p,s,m.start())})
 for m in re.finditer(r'(?:this\.)?(\w+)\.(\w+)\s*\+=\s*(?:new\s+[\w.<>]+\s*\(\s*)?(?:this\.)?(\w+)',s):
  events.append({'owner':owner,'control':m[1],'event':m[2],'handler':m[3],'evidence':loc(p,s,m.start())})
reachable={'Program','MainForm','Login_Form','CityRecordWindow'}
while True:
 nxt=reachable|{e['target'] for e in edges if e['caller'] in reachable}
 if nxt==reachable:break
 reachable=nxt
for k,v in forms.items(): v['reachability']='potential-static-path' if k in reachable else 'no-static-path-or-helper-mediated; review'
uncompiled=[]
for extra in (ROOT/'MyWorkApplication').rglob('*.cs'):
 if extra in texts or any(x in extra.parts for x in ('bin','obj')): continue
 raw=extra.read_text(encoding='utf-8-sig',errors='replace'); stripped=mask(raw)
 for m in re.finditer(r'class\s+(\w+)\s*:\s*(?:System.Windows.Forms.)?Form\b',stripped):
  uncompiled.append({'name':m[1],'definition':loc(extra,stripped,m.start()),'status':'not in csproj Compile; not active build'})
menus=[]
p=ROOT/'MyWorkApplication/MainForm.Designer.cs';s=clean[p]
for m in re.finditer(r'this\.(\w+)\s*=\s*new\s+System.Windows.Forms.ToolStripMenuItem',s):
 name=m[1]; bindings=[e for e in events if e['owner']=='MainForm' and e['control']==name and e['event']=='Click']
 menus.append({'control':name,'definition':loc(p,s,m.start()),'click':bindings,'targets':[e for e in edges if e['caller']=='MainForm' and any(b['handler']==e['method'] for b in bindings)],'kind':'bound-action' if bindings else 'container-or-unbound'})
tree={}
for m in re.finditer(r'this\.(\w+)\.(?:DropDownItems|Items)\.AddRange\([^;]+?\{([^}]+)\}',s):
 for child in re.findall(r'this\.(\w+)',m[2]):tree[child]=m[1]
for item in menus:
 chain=[]; cur=item['control']
 while cur in tree and cur not in chain: chain.append(cur);cur=tree[cur]
 item['menu_path']=' > '.join([cur]+list(reversed(chain)))
 item['attached_to_visible_menu']=cur=='menuStrip1'
 item['designer_hidden']=bool(re.search(r'this\.'+re.escape(item['control'])+r'\.Visible\s*=\s*false',s))
report={'uncompiled_forms':uncompiled,'source_root':str(ROOT),'method':'Lexical enumeration of csproj Compile entries; comments and literals masked. Potential form reachability deliberately over-approximates execution; method calls, visibility, runtime branching and helper-mediated navigation need review. No desktop or network executed.','compiled_files':len(files),'missing_files':[str(p.relative_to(ROOT)) for p in files if not p.exists()],'file_hashes':{p.relative_to(ROOT).as_posix():hashlib.sha256(p.read_bytes()).hexdigest() for p in texts},'forms':sorted(forms.values(),key=lambda x:x['name']),'menu_items':menus,'form_construction_edges':edges,'actions':actions,'role_check_sites':permissions,'events':events}
(OUT/'p0-1-workflow-index.json').write_text(json.dumps(report,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
md=['# P0.1 generated screen and menu matrix','',report['method'],'','## Every compiled Form subclass','', '| Screen | Source | Potential incoming paths | Status |','|---|---|---|---|']
for v in report['forms']:
 refs='; '.join(f"{e['caller']}.{e['method']} → {e['evidence']}" for e in v['incoming']) or 'None'
 md.append(f"| {v['name']} | {v['definition']} | {refs} | {v['reachability']} |")
md+=['','## Forms excluded from project compilation','','| Screen | Source | Status |','|---|---|---|']
for v in uncompiled: md.append(f"| {v['name']} | {v['definition']} | {v['status']} |")
md+=['','## Every MainForm menu item','', '| Menu identifier / attachment | Definition | Handler | Form targets / disposition |','|---|---|---|---|']
for v in menus:
 md.append(f"| {v['control']} ({'attached' if v['attached_to_visible_menu'] else 'detached legacy menu'}{' / hidden' if v['designer_hidden'] else ''}) | {v['definition']} | {'; '.join(e['handler'] for e in v['click']) or 'None'} | {'; '.join(e['target']+' at '+e['evidence'] for e in v['targets']) or v['kind']} |")
md+=['','## Print/export control bindings (including nonmatching handler names)','','| Owner/control | Event/handler | Evidence |','|---|---|---|']
for e in events:
 if re.search(r'Print|Export',e['control'],re.I) and e['event']=='Click': md.append(f"| {e['owner']}.{e['control']} | {e['event']} / {e['handler']} | {e['evidence']} |")
md+=['','## Report, print and export entry methods','', '| Owner | Method | Evidence |','|---|---|---|']
for a in actions:
 if re.search('Export|Print',a['method'],re.I):md.append(f"| {a['owner']} | {a['method']} | {a['evidence']} |")
(OUT/'p0-1-screen-menu-matrix.md').write_text('\n'.join(md)+'\n',encoding='utf-8')
assert not report['missing_files']
assert len(forms)==len(report['forms'])
assert all(x['definition'] and x['reachability'] for x in report['forms'])
print(json.dumps({'compiled_files':len(files),'forms':len(forms),'menu_items':len(menus),'bound_menu_actions':sum(bool(x['click']) for x in menus),'events':len(events),'form_edges':len(edges),'action_methods':len(actions),'role_sites':len(permissions),'uncertain_forms':[v['name'] for v in forms.values() if v['name'] not in reachable]},indent=2))
