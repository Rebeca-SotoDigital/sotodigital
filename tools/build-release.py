#!/usr/bin/env python3
"""Build a reviewable web-only zip from tracked files, without publishing."""
from pathlib import Path,PurePosixPath
import subprocess,zipfile,fnmatch,hashlib,re,json
ROOT=Path(__file__).resolve().parents[1]
OUTPUT=ROOT/'dist';OUTPUT.mkdir(exist_ok=True)
patterns=[s.strip() for s in (ROOT/'.deployignore').read_text().splitlines() if s.strip() and not s.lstrip().startswith('#')]
def excluded(name):
 p=PurePosixPath(name)
 if any(part in ('.git','__MACOSX','node_modules') for part in p.parts):return True
 return any(name.startswith(pattern) if pattern.endswith('/') else fnmatch.fnmatch(name,pattern) or fnmatch.fnmatch(p.name,pattern) for pattern in patterns)
tracked=subprocess.check_output(['git','ls-files','-z'],cwd=ROOT).decode().split('\0')
files=[]
for name in sorted(n for n in tracked if n and not excluded(n)):
 p=ROOT/name
 if not p.exists():continue
 if p.is_symlink() or not p.is_file():raise RuntimeError('Unexpected source type: '+name)
 data=p.read_bytes()
 if re.search(rb'(?:xkeysib-[A-Za-z0-9_-]{24,}|sk-ant-[A-Za-z0-9_-]{24,}|-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----)',data):raise RuntimeError('Potential secret: '+name)
 if p.suffix in ('.php','.js','.css','.html','.xml','.txt') and b'/Applications/XAMPP/' in data:raise RuntimeError('Local installation path: '+name)
 files.append((name,data))
required=['index.php','.htaccess','api/lead.php','bin/campaign-leads.php','includes/asset-manifest.php','includes/campaign-emails.php']
for name in required:
 if name not in dict(files):raise RuntimeError('Missing required release file: '+name)
archive=OUTPUT/'soto-digital-arsys.zip'
with zipfile.ZipFile(archive,'w',zipfile.ZIP_DEFLATED) as z:
 for name,data in files:
  info=zipfile.ZipInfo.from_file(ROOT/name,arcname=name);info.external_attr=0o100644<<16;info.compress_type=zipfile.ZIP_DEFLATED;z.writestr(info,data)
with zipfile.ZipFile(archive) as z:
 if z.testzip() is not None:raise RuntimeError('Archive integrity failed')
sha=hashlib.sha256(archive.read_bytes()).hexdigest()
(OUTPUT/'soto-digital-arsys.sha256').write_text(sha+'  '+archive.name+'\n')
(OUTPUT/'manifest.json').write_text(json.dumps({'archive':archive.name,'sha256':sha,'file_count':len(files),'files':[n for n,_ in files],'source_commit':subprocess.check_output(['git','rev-parse','HEAD'],cwd=ROOT,text=True).strip(),'working_tree_snapshot':True,'deployment_performed':False},indent=2)+'\n')
(OUTPUT/'soto-config.example.php').write_bytes((ROOT/'docs/soto-config.example.php').read_bytes())
(OUTPUT/'LEEME-ARSYS.md').write_text('''# Paquete preparado; NO publicado

El ZIP contiene únicamente archivos web versionados y la CLI operativa bin, protegida por Apache. No incluye código ni configuración sin seguimiento Git; en particular, el trabajo independiente de WhatsApp no forma parte de esta entrega.

1. Hacer copia del sitio y la BD actuales, confirmar PHP/extensiones, rewrite/headers y HTTPS.
2. Completar el ejemplo de configuración en una carpeta privada fuera de public_html. No subir el ejemplo dentro de la web, ni copiar datos/claves locales.
3. Autorizar la IP saliente de Arsys en Brevo. Crear un widget Turnstile de producción que admita soto-digital.com (el local no basta). Configurar SOTO_CONFIG_FILE para PHP web y CLI. Crear almacenamiento privado y cron de retry cada cinco minutos con las rutas de Arsys. Configurar backup diario, purge primero --dry-run y monitor protegido por cabecera.
4. Publicar el ZIP como una versión completa, no mezclar manifiestos ni borrar recursos de páginas abiertas. Respetar la protección de bin/includes/docs/tools.
5. Repetir brevo-check, health, prueba propia del formulario y entrega de los dos emails. Comprobar cookies, conversión, noindex y rutas privadas 404 antes de anuncios.
6. Rollback: parar anuncios y cron/escrituras, recuperar el paquete anterior completo. No sobrescribir la BD con una copia vieja sobre leads nuevos; preservar y reconciliar primero. Restaurar solo con conexiones cerradas y reaplicando supresiones.

El .sha256 verifica integridad; manifest.json enumera archivos. No son firma ni certificación de seguridad. Los borradores Ads/blog conservan noindex. El paquete no contiene bases de leads, secretos, logs, docs, pruebas ni el LaunchAgent del Mac.
''')
print(json.dumps({'archive':str(archive),'files':len(files),'bytes':archive.stat().st_size,'sha256':sha,'deployment_performed':False}))
