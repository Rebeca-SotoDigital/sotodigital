# Revisión editorial del primer artículo — 26/11/2026

## Aplicado

- Tratamiento a tú: «vuestro sistema de contactos» pasa a «tu sistema de contactos» y «cómo trabajáis» a «cómo trabajas».
- Cierre literal solicitado, resumen literal y nuevo punto 6 literal sin enlaces externos. Índice generado desde las secciones, con anclas existentes conservadas.
- Plazo del cierre compartido del blog conectado a `campaign_response_time()`. El único literal nuevo estaba en `includes/blog-post.php` («dos días laborables»); Gracias, emails, home y landings ya usaban esa función con valor aprobado «2 días laborables». No se cambia la configuración ni el formulario.
- El ejemplo y las tres figuras pendientes se renderizan solo con `soto_show_pending()`. En false desaparecen sus contenedores y encabezados completos. No hay imágenes ficticias ni src vacío en HTML. La figura futura acepta src, alt descriptivo y caption; se muestra solo con los tres campos cumplimentados.

## Datos por aportar

1. Ejemplo real de Soto Digital: qué se revisó o cambió, sin cifras no demostrables.
2. Captura: titular vago frente a titular claro.
3. Captura: botón ambiguo frente a botón con acción clara.
4. Captura: formulario móvil con teclado abierto.

No hay pendiente de plazo: el equipo confirmó mantener los dos días laborables.

## Archivos

Textos: `includes/blog-posts.php`. Plantilla y bloques condicionales: `includes/blog-post.php`. Estilos: `css/redesign.css`. Prueba reproducible: `tools/tests/blog-editorial.py`. Recursos derivados: CSS site/site-services con hash, `includes/asset-manifest.php` y `sitemap.xml`.

## Comprobado

Prueba con SHOW_PENDING_PLACEHOLDERS true y false, cuatro bloques completos, índice, ids únicos, cierre exacto, ausencia de tratamientos plurales y términos excluidos dentro del artículo, plazo configurable y rutas sin /mumito/.

## Comprobar a mano tras subir

Abrir `/blog/mi-web-recibe-visitas-pero-no-contactos.php` a 360 px y en escritorio. Revisar lectura, anchura de recuadros, saltos del índice y ausencia de desbordamientos. En local con SHOW_PENDING_PLACEHOLDERS=true deben verse cuatro bloques; en false no deben dejar huecos. Comprobar el cierre y su enlace al formulario, sin enviar solicitudes de prueba innecesarias.

No se pudo realizar inspección visual: la herramienta de navegador devuelve apps y browsers vacíos. No se afirma que se haya verificado el render a 360 px. Cambios preparados en local, pendientes de subida SFTP.

## Ilustraciones incorporadas

Tres imágenes generadas con IA, optimizadas a JPEG de 1200×800 y guardadas en `img/blog/`, sustituyen los huecos de capturas a petición del equipo. Alt y pies describen su carácter ilustrativo. No son resultados ni capturas reales. Solo permanece pendiente el ejemplo real. Estas imágenes se muestran también en producción con placeholders desactivados. Pendiente subida por SFTP.

## Segunda entrada — revisión confirmada

Archivos de esta revisión: `includes/blog-posts.php`, `includes/blog-post.php`, nuevo `includes/blog-settings.php`, `tools/tests/blog-editorial.py`, `tools/build-sitemap.py`, `sitemap.xml`, `pendientes.md` y este informe. No se modifican CSS ni recursos generados: se reutilizan los estilos existentes.

Cambios editoriales: vuestra capacidad → tu capacidad; vuestro trabajo → tu trabajo; acordad/podéis/vais/revisaréis → decide/puedes/vas/revisarás. Resumen literal, conversión explicada, párrafo sobre llamadas/WhatsApp y punto 7 literal. Cierre separado después del punto 7; solo «campañas» enlaza a publicidad-digital.php. Referencia al primer artículo en el punto 4 y enlace oficial de Google existente con target blank y rel noopener.

Pendientes del segundo artículo: ejemplo real «Así lo preparamos en Soto Digital»; captura anuncio/página coherentes frente a discordantes; esquema inversión Google/honorarios; captura de confirmación de formulario. No se generan imágenes de esta segunda entrada. Autor personal y cargo se configuran en un único lugar (`includes/blog-settings.php`), sin valor todavía. Fecha de publicación real pendiente. Fecha de actualización de los dos artículos revisados: 26/11/2026, autorizada por las instrucciones. No se usa mtime de despliegue como fecha editorial.

Con placeholders true aparecen autor, publicación y bloques pendientes; con false solo firma Soto Digital y fecha de actualización, sin contenedores pendientes. Schema conserva Organization Soto Digital en author/publisher, añade dateModified y omite datePublished hasta conocerlo. La firma/configuración de la plantilla se comparte con el resto del blog.

Ambas entradas comparten plantilla, resumen, notas/figuras, firma, índice, cierre y relacionados. Diferencias de contenido: el primer artículo tiene ejemplo aprobado y tres ilustraciones autorizadas; el segundo conserva sus huecos y ejemplo pendientes.

Pruebas automatizadas correctas para ambos estados, enlaces locales, H1, índices, metadatos y plazo configurable. Pendiente inspección visual en navegador a 360 px y escritorio y subida SFTP. Probar a mano: índice punto 7, enlace cruzado al primer artículo, documento Google en otra pestaña, relacionados y botón final hacia /#diagnostico (o prefijo local). No enviar formularios para esta revisión.

## Cierre de pendientes por decisión del equipo

Firma definitiva corporativa Soto Digital (Organization); fechas desactivadas tanto en la línea visible como en BlogPosting, sin avisos pendientes. El ajuste se comparte en las tres entradas. El equipo autoriza ahora ilustraciones generadas para el segundo artículo, sustituyendo la prohibición anterior: tres JPEG 1200×800 en `img/blog/`, con alt/pie explicativo y carácter ilustrativo indicado. Recuadro completado con el proceso de trabajo ya acordado, sin inventar casos ni métricas. Pruebas de las tres entradas en ambos estados de placeholders correctas. Los pendientes editoriales anteriores quedan resueltos por estas decisiones. Sigue pendiente subir por SFTP y verificar visualmente.

## Tercera entrada — revisión confirmada

Modificados `includes/blog-posts.php`, `includes/blog-post.php`, `css/redesign.css`, `tools/tests/blog-editorial.py`, los CSS generados site/site-services, `includes/asset-manifest.php`, `sitemap.xml`, `pendientes.md` y este informe.

Aplicados definición de SEM, resumen literal, fila Ritmo, guía de tres opciones, cuatro preguntas en lista, párrafo local sin enlaces, frase final exacta y simplificación de hipótesis. No había tratamientos plurales en el contenido del artículo; el marcador del ejemplo conserva el texto del documento dirigido al equipo.

Enlaces existentes verificados: SEO y SEM, publicidad digital y las dos entradas anteriores. El destino SEO se enlaza solo en su primera mención del cuerpo; Google Ads en la sección correspondiente; no se repiten destinos en los párrafos del cuerpo. Los relacionados conservan enlaces por navegación. El único enlace externo sigue siendo la documentación oficial que ya existía, con pestaña nueva y noopener.

Tabla con caption, th/scope, región enfocables por teclado y overflow-x auto dentro de max-width 100%; ancho mínimo interno 580 px para conservar legibilidad, sin ensanchar la página. No se afirma verificación visual a 360 px: no hay navegador conectado.

La plantilla de figura admite ahora el marcador «ilustración» además del de captura. Pendientes: ejemplo «Así lo decidimos en Soto Digital»; ilustración de vías orgánica/publicitaria hacia una web; ilustración de decisión de tres ramas. Se ocultan contenedores y títulos completos con SHOW_PENDING_PLACEHOLDERS=false. No se generan imágenes ni se escribe el ejemplo para esta revisión.

Se mantiene la decisión más reciente del equipo: Soto Digital como autor/editor y sin fechas ni marcadores de fecha. El plazo sigue conectado a campaign_response_time(), sin cambios en integraciones. Los tres artículos usan plantilla, resumen, notas/figuras, firma, índice, relacionados y CTA comunes; las únicas diferencias son el contenido y que los dos primeros ya tienen ilustraciones y recuadros completados.

Pruebas: tres artículos, ambos estados del interruptor, índice, H1, orden de secciones, tabla, frases exactas, enlaces y plazo compartido. Probar a mano tras subir: lectura a 360 px/escritorio, desplazamiento horizontal de tabla sin desplazamiento de toda la página, foco con Tab, índice de guía rápida, enlaces relacionados y CTA que llega al formulario /#diagnostico. Sin envío de leads.

## Tercera entrada: contenido completado

El equipo solicita completar los pendientes y autoriza las ilustraciones. Añadido recuadro de proceso basado en el cuestionario y la propuesta ya acordados (sin casos ni resultados inventados). Dos JPEG 1200×800 generados con IA en `img/blog/vias-seo-publicidad.jpg` y `img/blog/decidir-seo-campana.jpg`, señalados como ejemplos ilustrativos. Pruebas de las tres entradas en true/false correctas y sin marcadores pendientes. La comprobación visual a 360 px/escritorio y subida a Arsys siguen pendientes.

- Ajuste de pies de imagen solicitado: «Ejemplo ilustrativo:» en las tres entradas. Se mantiene la procedencia de las imágenes documentada en este informe, sin añadirla al pie visible.
