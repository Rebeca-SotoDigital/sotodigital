# Cuestionario, Brevo, Claude y medición

Actualizado: 21 de noviembre de 2026. Implementación local; sin claves reales ni llamadas de prueba a cuentas externas. Los archivos mencionados en el mensaje de Claude no fueron adjuntados: se ha implementado el flujo sobre el proyecto PHP existente.

## Flujo implementado

Home y landings → cuestionario de cinco pasos → `api/lead.php` → guardado privado SQLite → página de gracias. Un proceso CLI privado toma la cola y hace resumen con Claude, creación/actualización en Brevo y los dos emails transaccionales. La puntuación la calcula PHP antes de guardarla. No se necesita n8n para este flujo.

El navegador no espera a las APIs externas. El éxito significa que se ha guardado la solicitud localmente, no que todos los proveedores ya la hayan procesado. La tarea programada es necesaria para que lleguen los emails y se sincronice Brevo. La caída de un proveedor no debe impedir los otros caminos. La configuración de captura requiere almacenamiento y Turnstile; no depende de que Claude esté disponible.

Pasos: sector, objetivo, inversión mensual orientativa, urgencia y contacto. Nombre, email, móvil y aceptación son obligatorios; web y contexto adicional, opcionales. «Tiene web» se calcula a partir del campo URL, sin un paso extra. Sin JavaScript todas las preguntas se muestran juntas y el envío normal sigue disponible.

## Configuración privada

Copiar `docs/soto-config.example.php` fuera del directorio público, con el nombre `soto-config.php`. Completar los valores en el servidor y definir `SOTO_CONFIG_FILE` con la ruta absoluta del archivo privado. También se aceptan variables de entorno con los mismos nombres, que tienen prioridad. No guardar secretos en el ejemplo ni en JS. La configuración dentro del proyecto o DOCUMENT_ROOT se rechaza.

- Captura: `CAMPAIGN_DATA_DIR`, `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `CAMPAIGN_ALLOWED_HOSTS`.
- Límite: `CAMPAIGN_RATE_SECRET` opcional; por defecto se utiliza el secreto de Turnstile para HMAC.
- CRM: `BREVO_API_KEY`, `BREVO_LIST_ID` (número de la lista Leads web).
- Emails: la misma clave Brevo, `CAMPAIGN_MAIL_FROM` autorizado en Brevo y `CAMPAIGN_MAIL_TO` (por defecto ads@soto-digital.com).
- IA: `ANTHROPIC_API_KEY`, `ANTHROPIC_MODEL` (por defecto claude-haiku-5-5, configurable).
- Medición: `GOOGLE_ANALYTICS_ID`, `GOOGLE_ADS_ID`, `GOOGLE_ADS_LEAD_LABEL` y `GOOGLE_ADS_WHATSAPP_LABEL`.

Se necesita PHP con cURL, mbstring, PDO SQLite y funciones JSON de SQLite. El almacenamiento debe estar fuera del directorio público, con permisos restringidos. Se mantiene el historial local de solicitudes, incluso si varios leads comparten email. Brevo refleja los atributos de la solicitud más reciente de ese email; no es un historial de oportunidades completo.

## Brevo: lista y atributos

Crear una lista exclusiva **Leads web**, sin automatizaciones de newsletters ni campañas promocionales. Copiar su ID numérico. Crear estos atributos antes de sincronizar; los nombres y tipos deben coincidir:

| Tipo Brevo | Atributos |
| --- | --- |
| Texto | NOMBRE_COMPLETO, MOVIL_CONTACTO, WEB_NEGOCIO, SERVICIO, SECTOR, OBJETIVO, PRESUPUESTO, URGENCIA |
| Número | PUNTUACION |
| Texto | POTENCIAL, VERSION_PUNTUACION, RESUMEN_IA, SIGUIENTE_PASO_IA, ESTADO_IA |
| Booleano | TIENE_WEB |
| Texto | UTM_SOURCE, UTM_MEDIUM, UTM_CAMPAIGN, UTM_CONTENT, UTM_TERM, GCLID, GBRAID, WBRAID |
| Fecha | FECHA_SOLICITUD |
| Texto | ID_SOLICITUD, VERSION_PRIVACIDAD |

El email es el identificador del contacto y no necesita atributo personalizado. MOVIL_CONTACTO se usa como texto, no como alta en SMS. Los valores de sector, objetivo, presupuesto y urgencia son las claves del cuestionario; consultar `includes/campaign-questionnaire.php` para las etiquetas. POTENCIAL usa caliente, templado o frio.

El contacto se crea/actualiza con `updateEnabled=true`. No se cambian las listas de bloqueo de marketing, no se añade a listas de newsletters ni se dispara una campaña. Revisar las automatizaciones de la propia cuenta: añadir un contacto a una lista podría activar un flujo que ya existiera allí.

Dar de alta/verificar el remitente y dominio para email transaccional, siguiendo las instrucciones DNS de Brevo sin romper el correo existente. El aviso va a ads@soto-digital.com y la respuesta del interesado vuelve a esa dirección. ai@soto-digital.com se publica como contacto para temas de IA; no recibe copia automática de cada lead.

## Puntuación determinista provisional

No se ha facilitado la tabla original acordada con Claude. Se incluye esta tabla editable en `includes/campaign-questionnaire.php`, pendiente de aprobación:

| Criterio | Respuesta | Puntos |
| --- | --- | --- |
| Inversión | Menos de 300 € / por definir | 0 |
| Inversión | 300–600 € | 2 |
| Inversión | 600–1.500 € | 3 |
| Inversión | Más de 1.500 € | 4 |
| Urgencia | Lo antes posible | 3 |
| Urgencia | Próximo mes | 2 |
| Urgencia | 1–3 meses | 1 |
| Urgencia | Explorando | 0 |
| Objetivo | Consultas / ventas | 3 |
| Objetivo | Visibilidad | 1 |
| Objetivo | Orientación | 0 |

Total 0–10: caliente 7–10, templado 4–6, frio 0–3. Sector, nombre, existencia de web y datos personales no suman puntos. Se guarda la versión de reglas junto a la puntuación. Los rangos de inversión son respuestas de cualificación, no precios de Soto Digital. La puntuación orienta revisión humana y no rechaza automáticamente por presupuesto.

## Claude

Solo redacta resumen y siguiente paso: no decide ni modifica categoría. Se envían sector, objetivo, inversión, urgencia, existencia de web, servicio y puntuación. No se mandan nombre, email, móvil, URL, texto libre, identificadores de campaña ni IP.

La respuesta debe ser JSON con `resumen` y `siguiente_paso`; se valida y limita longitud. Tres intentos máximos por solicitud. Si falla o no hay clave, el lead y su puntuación siguen guardados, Brevo puede recibirlo sin resumen y el email informa «No disponible». Una recuperación posterior puede actualizar el resumen en Brevo sin reenviar emails ya entregados. Para reactivar una IA agotada es necesaria revisión técnica; no hay botón público.

## Cola y emails

Configurar cron cada cinco minutos (o la frecuencia elegida) con PHP CLI y el mismo SOTO_CONFIG_FILE/variables que PHP web:

```sh
php tools/campaign-leads.php retry
php tools/campaign-leads.php status
php tools/campaign-leads.php export
```

`retry` tiene bloqueo de fichero para evitar dos trabajadores simultáneos; procesa hasta 50 pendientes por ejecución, según los proveedores configurados, rotando los intentos para que fallos antiguos no bloqueen solicitudes nuevas. `status` muestra recuentos, sin contactos. `export` escribe CSV en stdout: solo guardar en un destino privado. No existe panel público ni endpoint de exportación.

Los emails salen por Brevo, no mail() ni Resend. Hay una clave de idempotencia determinista en cada mensaje. Los envíos con respuesta ambigua (timeout, error de servidor o proceso interrumpido) quedan con estado `sending`: revisar el panel de Brevo antes de reponerlos en `pending`, para evitar duplicados fuera de la ventana de deduplicación del proveedor. Fallos inequívocos permanecen pendientes. Esta revisión y la reparación de errores de cuenta/atributos requieren una persona; la tarea no puede arreglar credenciales ni DNS.

## Antispam y fallos

- CSRF y token de solicitud de sesión; reenviar la misma solicitud aceptada devuelve la misma referencia.
- Límite persistente: cinco intentos por conexión/IP en una ventana de una hora, con HMAC y sin IP en claro en esa tabla. Incluye intentos inválidos después de validar la sesión.
- No se confía en X-Forwarded-For enviado por el visitante. Si hay proxy/CDN, configurar correctamente REMOTE_ADDR en el servidor solo desde proxies de confianza; si no, diferentes visitantes podrían compartir el límite.
- Turnstile se comprueba en servidor: acción proposal y hostname autorizado.
- Campo trampa, nombres sin letras y dominios temporales conocidos se descartan sin guardar el contacto ni generar conversión. El listado local no detecta todos los dominios temporales.
- El éxito silencioso usa la misma respuesta pública y pantalla de agradecimiento, pero no emite un recibo de conversión real.
- Ningún dato personal se incluye en logs de errores del código. Revisar aparte logs del servidor y del proveedor.

## Consentimiento y medición

Consent Mode v2 básico, con las cuatro señales denegadas al inicio; la librería de Google no se carga hasta aceptar una categoría configurada. Analíticas concede analytics_storage; Publicitarias concede ad_storage y ad_user_data. ad_personalization permanece denegado. La nueva versión 3 de preferencias pide una elección nueva a preferencias antiguas.

GA4 recibe `generate_lead` solo en gracias con un recibo de una solicitud realmente guardada; Google Ads recibe su conversión directa si existe ID/label y permiso publicitario. El marcador se consume en el servidor y hay deduplicación por sesión y transaction_id. No se emite conversión por clic en Enviar ni por spam descartado. Google no recibe el formulario ni la puntuación. WhatsApp usa evento GA4 `whatsapp_click` y label Ads propio; tratarlo como acción separada/secundaria, porque no demuestra que se haya enviado un mensaje.

Elegir una fuente de conversión principal: no importar además generate_lead como conversión principal si ya se utiliza la etiqueta directa. Verificar con Tag Assistant y con consentimiento aceptado, rechazado y retirado. En modo básico es normal no medir todas las solicitudes sin permiso. Medir leads reales también desde SQLite/Brevo.

La atribución se mantiene durante 30 minutos en la sesión para navegar entre landings sin perder el origen; no se inventa una campaña si no se recibió. Validar clics reales y redirecciones en producción.

## Pendientes de activación

Cuentas y claves, lista/atributos Brevo, remitente/DNS, almacenamiento privado, cron, IDs/labels Google, privacidad definitiva, revisión visual y prueba real controlada. También aprobación de la tabla provisional y alcance de la mini-auditoría, vídeos y testimonios auténticos. Todo ello se detalla en `pendientes.md`.

Fuentes de implementación:

- [API de contactos Brevo](https://developers.brevo.com/reference/create-contact).
- [Email transaccional Brevo](https://developers.brevo.com/reference/send-transac-email).
- [Modelos de Claude](https://platform.claude.com/docs/en/models/overview).
- [Consent Mode de Google](https://developers.google.com/tag-platform/security/guides/consent).
