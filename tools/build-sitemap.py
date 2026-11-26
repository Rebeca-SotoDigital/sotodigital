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
pages=[*ROOT.glob('*.php'),*(ROOT/'blog').glob('*.php')]
for page in sorted(pages,key=lambda p:(p!=ROOT/'index.php',str(p))):
    if page.name=='404.php':continue
    source=page.read_text()
    if not any(marker in source for marker in ['$service = [','$pageTitle =','project-page.php','blog-post.php']):continue
    result=subprocess.run([PHP,str(page)],cwd=ROOT,capture_output=True,text=True,check=True)
    if result.stderr:raise RuntimeError(result.stderr)
    html=result.stdout
    if re.search(r'<meta[^>]+name="robots"[^>]+content="[^"]*noindex',html):continue
    canonical=re.search(r'<link rel="canonical" href="([^"]+)"',html)
    if not canonical:raise RuntimeError('Missing canonical '+page.name)
    dependencies=[page,*(ROOT/'includes'/name for name in ['header.php','footer.php','site-content.php'])]
    if '$service = [' in source:
        dependencies.append(ROOT/'includes/service-page.php')
    if 'project-page.php' in source:
        dependencies.append(ROOT/'includes/project-page.php')
    if page.name in {'index.php','diseno-web.php','redes-sociales.php'}:
        dependencies.append(ROOT/'includes/project-section.php')
    if 'project-page.php' in source or page.name in {'index.php','diseno-web.php','redes-sociales.php'}:
        dependencies.extend(ROOT/'includes'/name for name in ['projects.php','project-details.php','project-card.php'])
    if page.name == 'index.php':
        dependencies.extend(ROOT/'includes'/name for name in ['campaign-content.php','team-section.php'])
    if page.parent.name == 'blog':
        dependencies.extend(ROOT/'includes'/name for name in ['blog-posts.php','blog-post.php','blog-settings.php'])
    modified=max(p.stat().st_mtime for p in dependencies)
    node=ET.SubElement(root,'{'+namespace+'}url')
    ET.SubElement(node,'{'+namespace+'}loc').text=canonical.group(1)
    ET.SubElement(node,'{'+namespace+'}lastmod').text=datetime.fromtimestamp(modified,timezone.utc).isoformat(timespec='seconds')
ET.indent(root,space='    ')
ET.ElementTree(root).write(ROOT/'sitemap.xml',encoding='UTF-8',xml_declaration=True)
print('Sitemap:',len(root),'public pages, real file modification times')
