# Landings de campañas de Soto Digital

Implementación local. No se ha publicado ni creado ninguna campaña ni contratado ningún proveedor.

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

## Cuestionario e integraciones (actualizado 21/11/2026)

El formulario se ha sustituido por cinco pasos: sector, objetivo, inversión orientativa, urgencia y contacto. Nombre, email y móvil obligatorios; web y contexto opcionales. Mini-auditoría inicial gratuita confirmada, sin precios inventados.

El endpoint es `api/lead.php`, con compatibilidad para `campanas/enviar.php`. Guarda primero en SQLite privado y confirma recepción. Las llamadas a Claude y Brevo y los emails se procesan con el worker `php tools/campaign-leads.php retry`, que debe programarse. Brevo sustituye a Resend; no se utiliza mail().

Reglas y configuración actuales, atributos Brevo, antispam y pruebas en `docs/integracion-leads.md`. La puntuación la decide PHP con una tabla provisional editable; Claude solo resume. Para guardar bastan almacenamiento y Turnstile válidos; para completar CRM, IA y emails hay que configurar sus cuentas/claves y ejecutar la cola.

La medición tiene Consent Mode v2 básico y eventos de solicitud válida/WhatsApp desarrollados, pero faltan IDs/labels y prueba real. No instalar otra etiqueta que duplique el sistema ni contar la recarga de gracias como una conversión nueva.

## Casos de éxito y clientes

`includes/campaign-content.php` contiene los casos documentados de restauración y distribución técnica. Los textos de casos no nombran al cliente y describen entregables comprobables, sin atribuir mejoras de ventas, tráfico o procedencia de captación desconocidas. Se muestran solo en servicios relacionados. En la parte inferior de la principal figuran Cantabria Kebab y TecniRefinish, ya publicados en la web; confirmar autorización y añadir métricas verificadas si se quieren presentar como resultados comerciales. No mezclar negocios propios con clientes externos.

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
- Faltan credenciales, vídeo real y validación con proveedor en producción. No afirmar un aumento de conversión sin medición real.

Documentación oficial:
- https://support.google.com/google-ads/answer/7337243
- https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
- https://developers.brevo.com/reference/send-transac-email

Validación realizada en local: once landings con HTTP 200 y recursos disponibles, noindex en HTML/cabeceras, rechazo de POST sin configuración y con CSRF inválido, gracias protegida y regresión de páginas públicas. Pruebas de validación y almacenamiento/envío con transporte simulado superadas. Revisión visual móvil/escritorio pendiente: el entorno no dispone de navegadores conectados.

Actualización 21/11/2026: las comprobaciones locales del nuevo cuestionario y los rechazos del endpoint se han repetido. Backend y lógica JS probados con transportes simulados; pendiente revisión visual y circuito real con cuentas. Consultar `docs/integracion-leads.md` y `pendientes.md` como referencia vigente para Brevo/Claude y medición.
