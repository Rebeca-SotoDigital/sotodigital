#!/usr/bin/env python3
from pathlib import Path
import subprocess,os,re,html,json,hashlib,xml.etree.ElementTree as ET
ROOT=Path(__file__).resolve().parents[2];PHP=os.environ.get('SOTO_PHP','php');NODE=os.environ.get('SOTO_AUDIT_NODE','node')
outputs=[]
tracked=set(subprocess.check_output(['git','ls-files','-z'],cwd=ROOT).decode().split('\0'))
for command in [[PHP,'tools/tests/campaign-config.php'],[PHP,'tools/tests/campaign-worker.php'],[PHP,'tools/tests/campaign-backup.php'],[PHP,'tools/tests/campaign-emails.php'],[PHP,'tools/tests/campaign-brevo.php'],[PHP,'tools/tests/site-paths.php'],[PHP,'tools/tests/campaign-leads.php'],[PHP,'tools/tests/campaign-audit.php'],[NODE,'tools/tests/campaign-frontend.js'],[NODE,'tools/tests/campaign-bootstrap.js']]:
 r=subprocess.run(command,cwd=ROOT,capture_output=True,text=True);outputs.append(r.stdout+r.stderr)
 if r.returncode:raise RuntimeError(outputs[-1])
files=sorted({ROOT/name for name in tracked if name.endswith('.php') and (ROOT/name).is_file()} | set((ROOT/'tools/tests').glob('*.php')))
for file in files:
 r=subprocess.run([PHP,'-l',str(file)],capture_output=True,text=True)
 if r.returncode:raise RuntimeError(r.stdout+r.stderr)
outputs.append(f'PASS: PHP lint {len(files)} files')
manifest=json.loads(subprocess.run([PHP,'-r',"echo json_encode(require 'includes/asset-manifest.php');"],cwd=ROOT,capture_output=True,text=True,check=True).stdout)
for source,current in manifest.items():
 path=ROOT/current;assert path.is_file(),current
 assert hashlib.sha256(path.read_bytes()).hexdigest()[:12] in path.name,current
 stem=Path(source).stem;suffix=Path(source).suffix
 versions=[p for p in path.parent.glob(stem+'.*'+suffix) if re.match(r'^'+re.escape(stem)+r'\.[a-f0-9]{12}'+re.escape(suffix)+r'$',p.name)]
 assert len(versions)<=2,(source,len(versions))
outputs.append('PASS: manifest hashes and maximum two versions')
whatsapp=subprocess.run([PHP,'-r',"require 'includes/site-content.php';echo SOTO_WHATSAPP_NUMBER;"],cwd=ROOT,capture_output=True,text=True,check=True).stdout
for page in ROOT.glob('*.php'):
 if page.name=='404.php' or page.name not in tracked:continue
 r=subprocess.run([PHP,str(page)],cwd=ROOT,capture_output=True,text=True,check=True);assert not r.stderr,r.stderr
 title=html.unescape(re.search(r'<title>(.*?)</title>',r.stdout).group(1));desc=html.unescape(re.search(r'<meta name="description" content="([^"]*)"',r.stdout).group(1))
 assert len(title)<=60 and len(desc)<=155,(page.name,len(title),len(desc))
 assert len(re.findall(r'<h1\b',r.stdout))==1,page.name
 assert not re.search(r'href="tel:',r.stdout),page.name
 for link in re.findall(r'href="(https://wa\.me[^"]*)"',r.stdout):
  assert whatsapp and (link=='https://wa.me/'+whatsapp or link.startswith('https://wa.me/'+whatsapp+'?')),(page.name,'Unexpected WhatsApp destination')
 assert '<base ' not in r.stdout,page.name
outputs.append('PASS: title/description limits, unique H1, no telephone CTAs, WhatsApp only to configured destination, no base tag')
ns={'s':'http://www.sitemaps.org/schemas/sitemap/0.9'}
urls=[e.text for e in ET.parse(ROOT/'sitemap.xml').findall('s:url/s:loc',ns)]
assert len(urls)==len(set(urls))
assert all('/campanas/' not in u and '/api/' not in u and '/tarjeta/' not in u for u in urls)
outputs.append(f'PASS: sitemap {len(urls)} unique public pages')
text='\n'.join(outputs)+'\n';print(text)
(ROOT/'docs/auditoria-2026-11/final-tests.txt').write_text(text)
