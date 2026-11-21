#!/usr/bin/env python3
"""Generate sitemap.xml from public entry pages and actual file modification times."""
from pathlib import Path
from datetime import datetime,timezone
import re,subprocess,os,xml.etree.ElementTree as ET
ROOT=Path(__file__).resolve().parents[1]
PHP=os.environ.get('SOTO_PHP','php')
namespace='http://www.sitemaps.org/schemas/sitemap/0.9'
ET.register_namespace('',namespace)
root=ET.Element('{'+namespace+'}urlset')
for page in sorted(ROOT.glob('*.php'),key=lambda p:(p.name!='index.php',p.name)):
    if page.name=='404.php':continue
    source=page.read_text()
    if not any(marker in source for marker in ['$service = [','$pageTitle =','project-page.php']):continue
    result=subprocess.run([PHP,str(page)],cwd=ROOT,capture_output=True,text=True,check=True)
    if result.stderr:raise RuntimeError(result.stderr)
    html=result.stdout
    if re.search(r'<meta[^>]+name="robots"[^>]+content="[^"]*noindex',html):continue
    canonical=re.search(r'<link rel="canonical" href="([^"]+)"',html)
    if not canonical:raise RuntimeError('Missing canonical '+page.name)
    dependencies=[page,ROOT/'includes/header.php',ROOT/'includes/footer.php']
    if '$service = [' in source:dependencies.append(ROOT/'includes/service-page.php')
    modified=max(p.stat().st_mtime for p in dependencies)
    node=ET.SubElement(root,'{'+namespace+'}url')
    ET.SubElement(node,'{'+namespace+'}loc').text=canonical.group(1)
    ET.SubElement(node,'{'+namespace+'}lastmod').text=datetime.fromtimestamp(modified,timezone.utc).isoformat(timespec='seconds')
ET.indent(root,space='    ')
ET.ElementTree(root).write(ROOT/'sitemap.xml',encoding='UTF-8',xml_declaration=True)
print('Sitemap:',len(root),'public pages, real file modification times')
