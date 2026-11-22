# Landings de campañas de Soto Digital

Implementación local. No se ha publicado la nueva web ni activado ninguna campaña. Brevo, Cloudflare y Claude están configurados para las pruebas locales.

## Rutas

Principal: `/campanas/index.php`.

| Intención / grupo de anuncios | Destino | Semillas para el Planificador |
| --- | --- | --- |
| Marketing digital / multicanal | `/campanas/index.php` | agencia marketing digital; agencia marketing digital Cantabria |
| Google Ads | `/campanas/google-ads.php` | agencia Google Ads; gestión Google Ads Cantabria |
| Diseño web | `/campanas/diseno-web.php` | diseño web Cantabria; presupuesto página web empresa |
| Redes sociales | `/campanas/redes-sociales.php` | agencia redes sociales Cantabria; gestión redes sociales empresas |
| Branding y diseño gráfico | `/campanas/branding.php` | agencia branding Cantabria; diseño imagen corporativa |
| Publicidad Instagram / Meta | `/campanas/meta-ads.php` | agencia Meta Ads; publicidad Instagram empresas |
| Email marketing | `/campanas/email-marketing.php` | agencia email marketing; servicio newsletters empresas |
| Contenidos | `/campanas/marketing-contenidos.php` | agencia marketing contenidos; redacción contenidos web empresas |
| SEO | `/campanas/seo.php` | agencia SEO Cantabria; posicionamiento web empresas |
| SEM | `/campanas/sem.php` | agencia SEM; publicidad buscadores Cantabria |
| Pantalla LED | `/campanas/pantalla-led.php` | publicidad pantalla LED Cantabria; anunciarse pantalla Raos |

No están enlazadas desde la web pública ni incluidas en sitemap. Llevan noindex/nofollow en HTML y cabeceras. Son URLs públicas, compartibles, sin comprobación de referer ni bloqueo por gclid: esos parámetros no autentican el origen y bloquearlos puede impedir revisar destinos de Ads. No añadir Disallow en robots.txt: el buscador debe poder leer noindex.

## Preparación del Planificador

Semillas propuestas, NO investigación de volúmenes realizada en una cuenta. No se han inventado CPC, previsiones, tasas de conversión ni incrementos. En la cuenta real: Herramientas > Planificador > Descubrir palabras clave, usar el grupo y su landing, seleccionar la zona de servicio, idioma español y red Google. Exportar volumen, competencia y rangos de puja; separar consultas informativas de intención de contratación. Revisar posibles negativas (empleo, curso, tutorial, gratis) según servicio. Empezar con grupos estrechos y concordancias a revisar. Evitar negativas compartidas que bloqueen un servicio que sí se ofrece.

Proyección: escenarios con presupuesto, CPC estimado y conversión supuesta, dejando claro origen, fecha y supuestos. Sin datos reales, no publicar estimaciones numéricas. Inversión publicitaria y honorarios separados.

Ejemplo de URL final: `https://soto-digital.com/campanas/google-ads.php`.
Sufijo final sugerido: `utm_source=google&utm_medium=cpc&utm_campaign={campaignid}&utm_content={creative}&utm_term={keyword}`. Mantener autoetiquetado si procede. El formulario guarda esos parámetros y el origen se conserva 30 minutos en sesión. La medición de Google ya está preparada con consentimiento y recibo único, pero requiere IDs/labels reales para activarse.

## Cuestionario e integraciones (actualizado 22/11/2026)

Cinco pasos: sector, objetivo, inversión orientativa, urgencia y contacto. Nombre y email obligatorios; móvil, empresa, web y contexto opcionales con la configuración actual. Mini-auditoría inicial gratuita: revisión de los datos aportados y primera respuesta en un máximo de 2 días laborables. Según las necesidades, llamada si hay móvil o propuesta por email. No se promete informe independiente ni propuesta completa en ese plazo.

`api/lead.php` guarda primero en SQLite privado y confirma recepción. El worker `php bin/campaign-leads.php retry` procesa Brevo, Claude y los dos emails; en local está programado cada cinco minutos mientras el Mac esté despierto y la sesión abierta. Puntuación PHP 0–10 con la tabla aprobada; Claude redacta únicamente resumen y siguiente paso. Brevo y entregas de emails probados con solicitudes internas. Los estados comerciales y responsable manual se conservan al sincronizar.

GA4 `G-1C1JJRTFPW` configurado en privado, recepción de `generate_lead` comprobada y consentimiento de aceptación/rechazo/retirada revisado por el equipo en Chrome local. Pendiente marcar el evento clave cuando aparezca y verificar etiquetas reales en producción. Google Ads pendiente de facturación y de ID/label. Sin WhatsApp ni teléfono públicos. No instalar etiquetas duplicadas ni medir la visita a gracias como un envío nuevo.

## Casos de éxito y clientes

`includes/campaign-content.php` contiene los casos documentados de restauración y distribución técnica. Los textos de casos no nombran al cliente y describen entregables comprobables, sin atribuir mejoras de ventas, tráfico o procedencia de captación desconocidas. Se muestran solo en servicios relacionados. Los clientes destacados de la principal permanecen ocultos mientras `approved` sea false; confirmar autorización y métricas verificables antes de mostrarlos. La landing de Google Ads no tiene casos de ese servicio documentados y no reutiliza proyectos web como prueba de rendimiento publicitario. No mezclar negocios propios con clientes externos.

## Vídeo en entrevista

La sección está preparada pero permanece oculta sin un archivo real. Grabar una entrevista breve por servicio. Propuesta de guion Google Ads:

1. ¿Para qué negocios tiene sentido anunciarse en Google?
2. ¿Qué revisáis antes de activar una campaña?
3. ¿Cómo decidís palabras clave, zonas y presupuesto?
4. ¿Por qué importan la landing y los otros canales?
5. ¿Qué puede esperar el cliente después de contactar?

Grabar a un miembro del equipo respondiendo a otra persona fuera de cámara, con subtítulos y audio claro. Introducir en `videos` la clave de servicio y ruta `img/videos/google-ads.mp4` y añadir `img/videos/google-ads.vtt` con subtítulos reales. No publicar un botón de reproducción ficticio ni material ajeno como si fuera nuestro.

## Comprobación

- `php tools/tests/campaign-leads.php`: validación, protección frente a cabeceras manipuladas, almacenamiento privado, doble envío, fallo parcial y recuperación de emails. Sin red ni envíos reales.
- Revisar todas las rutas, CSS, enlaces legales y formulario en móvil/escritorio.
- Con cuentas configuradas, probar un envío real controlado: Turnstile válido, lead guardado, aviso, acuse y gracias. Probar tokens inválidos, rechazo de hostname, error de almacenamiento y caída de email.
- Pendientes: vídeo real, evidencia de resultados de Google Ads y revisión del contenido por el equipo. Las credenciales locales ya funcionan; configuración y comprobaciones en producción siguen pendientes.
- Revisión del 22/11/2026: beneficios visibles, cuatro bloques de alcance, proceso específico auditoría/estrategia/proyección, seis preguntas frecuentes y CTA por ancla que conserva la URL y evita recargar. Honorarios separados de inversión, alcance de landing sujeto a propuesta, sin precios ni cifras inventadas. Se omite la pantalla LED en los canales de esta landing mientras su disponibilidad siga pendiente.
- Verificación local: HTTP 200 de las once landings y las dos variantes de Google Ads, formulario y recursos disponibles, HTML/cabecera noindex, un H1, sin WhatsApp/teléfono, vídeos/testimonios ausentes sin material aprobado. Suite estática y funcional automatizada correcta. Revisión visual actual en navegador pendiente; no hay una pestaña accesible al agente.

Ver `docs/revision-landing-google-ads.md` y `pendientes.md` para pendientes vigentes. No se ha desplegado ni enviado un formulario adicional por esta revisión.
