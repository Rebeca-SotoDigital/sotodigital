# Mejoras de home y landings — 25/11/2026

Aplicadas en local a partir de las capturas de escritorio y móvil aportadas por el equipo. No publicadas automáticamente ni comprobadas visualmente después del cambio: no hay navegador conectado.

1. Google Ads: titular «Google Ads para conseguir más consultas».
2. Acercar formulario móvil: titular más corto, bloque inicial más compacto y ejemplo ilustrativo retirado.
3. Beneficios con lista propia, separación de ubicación y plazo de respuesta.
4. CTA de propuesta amarillos; Continuar y botones secundarios diferenciados.
5. Menor separación vertical y mención multicanal resumida.
6. Ejemplo de anuncio retirado; no sustituido por resultados inventados.
7. WhatsApp retirado de landings de campañas, conservado en el sitio general.
8. Multicanalidad breve en landings digitales, ligada a objetivo y presupuesto; LED tiene alcance propio.
9. Formulario único de home después de Servicios y antes de Proyectos.
10. Tres proyectos iniciales en home; los otros tres en «Ver todos los proyectos», desplegable nativo accesible sin JavaScript. En otras páginas no se limita el portfolio.
11. Filas de servicios convertidas en enlaces completos, foco visible y texto «Ver servicio» con mayor zona pulsable.
12. Etiquetas Cliente / Proyecto propio usando los datos existentes, sin reclasificar negocios.
13. Plazo de primera respuesta configurable junto al formulario de home.
14. Alcance y FAQ específicos de los once servicios: se conserva el trabajo previo de Google Ads y se añaden contenidos para los otros diez. Sin tarifas, promesas ni métricas inventadas.
15. Revisión técnica de los cinco pasos: validación y retroceso, etiquetas y tipos de contacto, móvil opcional, privacidad, errores, éxito y deduplicación. Campos de texto a 16 px para evitar zoom en móviles, columna única en contacto móvil y Turnstile compacto con contenedor de menos de 300 px. Prueba VM de bootstrap verifica aparición solo en contacto, tamaños compacto/normal, sesión/widget únicos y error sin verificación. No resuelve CAPTCHA ni envía leads reales.

## Validación

Suite PHP/JS correcta, manifiesto con hashes regenerado y sitemap con 18 URL. Render de once landings sin advertencias: un formulario y cinco pasos por ruta, alcance y preguntas específicos, sin WhatsApp, slug conservado y multicanalidad digital. Home con tres tarjetas iniciales, seis accesibles al expandir y un único cuestionario antes del portfolio. Los registros de Brevo, SQLite y las claves no se han alterado.

## Publicación y comprobación final

Subir los archivos de `dist/mejoras-landings/` sobre `html/` conservando carpetas. Incluir manifiesto y todos los recursos con hash del paquete juntos; no borrar manualmente los recursos anteriores de páginas ya abiertas. No contiene configuración privada ni bases de leads.

Tras subir: recargar sin caché, revisar escritorio y móvil real (320, 390 y 768 px como referencias) y los cinco pasos, incluidos contacto y Turnstile. Comprobar los CTA, el desplegable de proyectos y la ausencia de WhatsApp en campañas. La validación de código y capturas previas no sustituye esta revisión visual final.

## Preferencia de amplitud

Restauradas las proporciones anteriores a petición del equipo: titulares, separación de columnas de 70 px, cambio a una columna a 800 px y espaciado original entre secciones. El contenedor general conserva su ancho anterior. Se mantienen las mejoras de contenido, navegación y formulario. Pendiente comprobar visualmente tras subir los archivos actualizados.

Ajustes posteriores solicitados: «Ver más» sin subrayado, botones con sus colores anteriores y tarjetas sin etiquetas de cliente/proyecto propio.
