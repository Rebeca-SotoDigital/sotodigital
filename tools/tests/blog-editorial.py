"""Check editorial content and both placeholder modes without external services."""
from pathlib import Path
import os,re,subprocess,html
root=Path(__file__).resolve().parents[2]
php=os.environ.get('SOTO_PHP','php')
entry='blog/mi-web-recibe-visitas-pero-no-contactos.php'
def render(pending,period=None):
 env={**os.environ,'SHOW_PENDING_PLACEHOLDERS':str(pending).lower()}
 if period:env['CAMPAIGN_RESPONSE_TIME']=period
 r=subprocess.run([php,entry],cwd=root,env=env,text=True,capture_output=True,check=True)
 assert not r.stderr,r.stderr
 return r.stdout
visible=render(True);hidden=render(False)
for value in [visible,hidden]:
 article=value.split('<main',1)[1].split('</main>',1)[0]
 assert not re.search(r'vuestro|vosotros|trabajáis|auditoría|mini auditoría',article,re.I)
 assert 'En Soto Digital podemos revisar contigo el recorrido de contacto de tu web. Cuéntanos qué servicio ofreces y qué consultas quieres recibir y preparamos una propuesta ajustada a tu negocio.' in article
 assert '6. ¿Carga rápido y hay otras formas de contactar?' in article
 ids=re.findall(r'\bid="([^"]+)"',value)
 assert len(ids)==len(set(ids))
 assert all(anchor in ids for anchor in re.findall(r'href="#([^"]+)"',value))
 assert article.index('En resumen')<article.index('En este artículo')
 assert article.index('id="apartado-velocidad-contacto"')<article.index('id="apartado-7"')
 section=article.split('id="apartado-velocidad-contacto"',1)[1].split('</section>',1)[0]
 assert 'href=' not in section
 assert '/mumito/' not in value
 assert not re.search(r'<img[^>]+src=""',value)
assert '[PENDIENTE: captura]' not in visible
for value in [visible,hidden]:
 assert len(re.findall(r'<figure class="blog-figure">',value))==3
 for image in re.findall(r'<figure class="blog-figure"><img src="([^"]+)"',value):
  assert (root/image.lstrip('/')).is_file()
 assert value.count('Ejemplo ilustrativo:')==3
assert '[PENDIENTE: captura]' not in visible
assert '[PENDIENTE: ejemplo' not in visible
assert 'Así lo revisamos en Soto Digital' in visible
assert '[PENDIENTE:' not in hidden
assert 'Así lo revisamos en Soto Digital' in hidden
assert 'blog-figure blog-pending' not in hidden
assert 'en un máximo de PLAZO DE PRUEBA.' in render(False,'PLAZO DE PRUEBA')
print('PASS: blog editorial copy, six checks/TOC, exact closing, three illustrative images and approved process example, no broken images, shared response time and configurable paths')

# Second article reuses the same template, metadata, time and placeholder switch.
import json
entry='blog/antes-de-invertir-en-google-ads.php'
visible=render(True);hidden=render(False)
assert '[PENDIENTE:' not in visible
assert len(re.findall(r'<figure class="blog-figure">',visible))==3
assert '[PENDIENTE: ejemplo real de Soto Digital.' not in visible
assert 'Por Soto Digital' in visible
assert '<time ' not in visible
assert '[PENDIENTE:' not in hidden
assert 'Así lo preparamos en Soto Digital' in visible
assert 'Así lo preparamos en Soto Digital' in hidden
assert 'blog-figure blog-pending' not in hidden
for value in [visible,hidden]:
 article=value.split('<main',1)[1].split('</main>',1)[0]
 assert not re.search(r'vuestro|vosotros|vuestra|acordad|podéis|revisaréis|auditoría',article,re.I)
 assert article.count('7. Un plan para las primeras semanas')==2
 assert 'prepara siete cosas' in article
 assert 'Si tu negocio también recibe llamadas o mensajes de WhatsApp, decide cómo vas a registrarlos, porque parte de las solicitudes puede llegar por ahí.' in article
 assert 'Configura la conversión, es decir, el registro de que alguien ha enviado la solicitud, para contar solo los envíos correctos y no cada visita a la página de confirmación.' in article
 assert 'href="/publicidad-digital.php">campañas</a>' in article
 assert 'target="_blank" rel="noopener"' in article
 assert len(re.findall(r'<h1\b',value))==1
 ids=re.findall(r'\bid="([^"]+)"',value)
 assert len(ids)==len(set(ids))
 assert all(a in ids for a in re.findall(r'href="#([^"]+)"',value))
 assert '/mumito/' not in value
 assert not re.search(r'<p>\s*<p>',article)
 schema=json.loads(re.search(r'<script type="application/ld\+json">(.*?)</script>',value,re.S).group(1))
 assert schema['author']['name']=='Soto Digital'
 assert schema['publisher']['name']=='Soto Digital'
 assert 'dateModified' not in schema
 assert schema['author']['@type']=='Organization'
 assert 'datePublished' not in schema
 links=re.findall(r'href="([^"]+)"',article)
 for url in links:
  if url.startswith('/') and not url.startswith('/#'):
   file=root/url.lstrip('/')
   assert file.is_file() or file.is_dir(),url
 assert len([url for url in links if url.startswith('https://')])==1
assert 'en un máximo de PLAZO DE PRUEBA.' in render(False,'PLAZO DE PRUEBA')
print('PASS: second article seven steps, explanatory conversion copy, valid links, optional blocks, shared author/time and honest structured dates')

for entry in ['blog/mi-web-recibe-visitas-pero-no-contactos.php','blog/antes-de-invertir-en-google-ads.php','blog/seo-o-google-ads-por-donde-empezar.php']:
 for pending in [True,False]:
  value=render(pending)
  assert '[PENDIENTE:' not in value
  assert 'Por Soto Digital' in value
  assert '<time ' not in value
  schema=json.loads(re.search(r'<script type="application/ld\+json">(.*?)</script>',value,re.S).group(1))
  assert 'datePublished' not in schema and 'dateModified' not in schema
  assert schema['author']['@type']=='Organization'
  for image in re.findall(r'<figure class="blog-figure"><img src="([^"]+)"',value):
   assert (root/image.lstrip('/')).is_file()
print('PASS: corporate signature, no dates and no production placeholders in any article, all illustrative assets exist')

entry='blog/seo-o-google-ads-por-donde-empezar.php'
visible=render(True);hidden=render(False)
assert '[PENDIENTE:' not in visible
assert len(re.findall(r'<figure class="blog-figure">',visible))==2
assert '[PENDIENTE: ejemplo real de Soto Digital.' not in visible
assert 'Así lo decidimos en Soto Digital' in visible
assert 'Así lo decidimos en Soto Digital' in hidden
assert '[PENDIENTE:' not in hidden and 'blog-figure blog-pending' not in hidden
for value in [visible,hidden]:
 article=value.split('<main',1)[1].split('</main>',1)[0]
 reader_copy=re.sub(r'\[PENDIENTE:[^\]]*\]','',article)
 assert not re.search(r'vuestro|vosotros|auditoría|mini auditoría',reader_copy,re.I)
 assert article.count('Una guía rápida para decidir')==2
 assert 'En resumen' in article
 assert 'En este artículo hablamos de SEO y de SEM, el conjunto de la publicidad de pago en buscadores, que en nuestro caso se centra en Google Ads.' in article
 assert 'los datos de las campañas pueden darte ideas que luego puedes comprobar con SEO. Eso no significa trasladar automáticamente cada palabra clave publicitaria al plan de SEO.' in article
 assert '<th scope="row">Ritmo</th>' in article
 assert '<caption>' in article and article.count('scope="col"')==3
 assert 'role="region" tabindex="0"' in article
 assert 'Para decidir, empieza por cuatro preguntas:</p><ul><li>¿Qué servicio quieres impulsar?</li><li>¿Qué funciona hoy?</li><li>¿Qué puedes invertir?</li><li>¿Cómo vas a medir las consultas?</li></ul><p>En Soto Digital te ayudamos a decidir por dónde empezar y a proponer los canales adecuados a tu situación.</p>' in article
 local='Si tu negocio es local, revisa también tu ficha de Google Business Profile: datos, horario, teléfono y enlace a la web actualizados. Muchas personas te encuentran allí antes de llegar a tu página.'
 assert '<p>'+local+'</p>' in article
 assert len(re.findall(r'<h1\b',value))==1
 ids=re.findall(r'\bid="([^"]+)"',value);assert len(ids)==len(set(ids))
 assert all(a in ids for a in re.findall(r'href="#([^"]+)"',value))
 for target in ['/seo-sem.php','/publicidad-digital.php','/blog/mi-web-recibe-visitas-pero-no-contactos.php','/blog/antes-de-invertir-en-google-ads.php']:
  assert 'href="'+target+'"' in article
  assert (root/target.lstrip('/')).is_file()
 assert len(re.findall(r'href="https://',article))==1
 assert 'target="_blank" rel="noopener"' in article
 assert '/mumito/' not in value
 assert article.index('id="apartado-6"')<article.index('id="apartado-guia"')<article.index('id="apartado-7"')
assert 'en un máximo de PLAZO DE PRUEBA.' in render(False,'PLAZO DE PRUEBA')
print('PASS: third article summary/SEM, comparison row, decision guide, four questions, valid links and completed process/illustrations')
