# Cuestionario, Brevo, Claude y medición

Actualizado: 21 de noviembre de 2026. Implementación local; sin claves reales ni llamadas de prueba a cuentas externas. Los archivos mencionados en el mensaje de Claude no fueron adjuntados: se ha implementado el flujo sobre el proyecto PHP existente.

## Flujo implementado

Home y landings → cuestionario de cinco pasos → `api/lead.php` → guardado privado SQLite → página de gracias. Un proceso CLI privado toma la cola y hace resumen con Claude, creación/actualización en Brevo y los dos emails transaccionales. La puntuación la calcula PHP antes de guardarla. No se necesita n8n para este flujo.

El navegador no espera a las APIs externas. El éxito significa que se ha guardado la solicitud localmente, no que todos los proveedores ya la hayan procesado. La tarea programada es necesaria para que lleguen los emails y se sincronice Brevo. La caída de un proveedor no debe impedir los otros caminos. La configuración de captura requiere almacenamiento y Turnstile; no depende de que Claude esté disponible.

Pasos: sector, objetivo, inversión mensual orientativa, urgencia y contacto. Nombre, email y aceptación son obligatorios; el móvil es opcional por defecto (`CAMPAIGN_PHONE_REQUIRED=false`, «Si prefieres que te llamemos»); empresa, web y contexto adicional, opcionales. «Tiene web» se calcula a partir del campo URL, sin un paso extra. Sin JavaScript todas las preguntas se muestran juntas, pero el envío protegido necesita JavaScript para Turnstile y los tokens. El aviso `noscript` lo explica. No se considera cumplido el envío completo sin JS: queda pendiente decidir una alternativa antispam accesible.

## Configuración privada

Copiar `docs/soto-config.example.php` fuera del directorio público, con el nombre `soto-config.php`. Completar los valores en el servidor y definir `SOTO_CONFIG_FILE` con la ruta absoluta del archivo privado. También se aceptan variables de entorno con los mismos nombres, que tienen prioridad. No guardar secretos en el ejemplo ni en JS. La configuración dentro del proyecto o DOCUMENT_ROOT se rechaza.

- Captura: `CAMPAIGN_DATA_DIR`, `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `CAMPAIGN_ALLOWED_HOSTS`.
- Límite: `CAMPAIGN_RATE_SECRET` opcional; por defecto se utiliza el secreto de Turnstile para HMAC.
- CRM: `BREVO_API_KEY`, `BREVO_LIST_ID` (número de la lista Leads web).
- Emails: la misma clave Brevo, `CAMPAIGN_MAIL_FROM` autorizado en Brevo y `CAMPAIGN_MAIL_TO` (por defecto ads@soto-digital.com).
- IA: `ANTHROPIC_API_KEY`, `ANTHROPIC_MODEL` (por defecto claude-haiku-5-5, configurable).
- Medición: `GOOGLE_ANALYTICS_ID`, `GOOGLE_ADS_ID`, `GOOGLE_ADS_LEAD_LABEL`.

Se necesita PHP con cURL, mbstring, PDO SQLite y funciones JSON de SQLite. El almacenamiento debe estar fuera del directorio público, con permisos restringidos. Se mantiene el historial local de solicitudes, incluso si varios leads comparten email. Brevo refleja los atributos de la solicitud más reciente de ese email; no es un historial de oportunidades completo.

## Brevo: atributos adaptados al contexto aportado

El archivo `lead.php` de referencia se ha integrado en el circuito existente, sin sustituir el guardado privado, la cola, CSRF, Turnstile ni idempotencia. No hay un segundo endpoint ni otra configuración privada. El límite vigente sigue siendo 5 intentos/hora, no el límite de 10 minutos del ejemplo.

Crear/verificar lista **Leads web**, sin automatizaciones de newsletters. El contacto se crea/actualiza por email con `updateEnabled=true`. Leer RGPD no es consentimiento para campañas. No se cambian listas de bloqueo ni se envían SMS.

### Atributos ya indicados como creados

| Atributo exacto | Tipo | Dato enviado |
| --- | --- | --- |
| EMPRESA | Texto | Campo opcional empresa/negocio |
| WEB_CLIENTE | Texto | Web indicada |
| SERVICIO_INTERES | Texto o categoría | Google Ads, SEO, Redes sociales, Diseño web, Marca, Email marketing u Otro |
| MENSAJE_LEAD | Texto | Contexto libre; nunca se envía a Claude |
| ORIGEN | Texto o categoría | Google Ads o Web |
| LANDING | Texto | Ruta de página donde se inicia formulario, sin query ni fragmento |
| UTM_CAMPAIGN | Texto | Campaña recibida |
| FECHA_ALTA_LEAD | Fecha | YYYY-MM-DD UTC de solicitud |
| RGPD_ACEPTADO | Booleano | true: información leída para esta solicitud, no alta promocional |
| FECHA_RGPD | Fecha | YYYY-MM-DD UTC de solicitud |
| ESTADO_LEAD | Texto o categoría | Nuevo, solo si no existe un estado informado |
| IA_NECESIDADES | Texto | Resumen de Claude, basado solo en categorías no identificativas |

FIRSTNAME y SMS son atributos estándar. FIRSTNAME recibe el nombre completo del campo actual: no se inventan ni separan apellidos, LASTNAME no se modifica. SMS recibe número internacional si es válido (móviles españoles de 9 cifras → +34, prefijo 00 → +). Si el formato no es reconocible, se conserva el teléfono en SQLite/aviso pero no se fuerza su formato en Brevo. Conflicto `duplicate_parameter` con SMS: un reintento sin SMS, conservando datos locales. No se añade a ninguna campaña SMS.

Servicios sin opción específica (Meta Ads, SEM, contenidos, LED y estrategia) se envían como Otro. Si existe el atributo técnico opcional SERVICIO, también recibe la clave exacta. Para no perder el detalle visible del servicio, se puede crear ese atributo de texto; ampliar categorías requiere acordar sus nombres y mapeo. Se acepta la etiqueta Redes si esa es la que se creó en lugar de Redes sociales.

### Nuevos atributos necesarios por calcular la puntuación nosotros

| Crear/verificar | Tipo | Valor |
| --- | --- | --- |
| PUNTUACION | Número | 0–10 calculado en PHP |
| POTENCIAL | Texto recomendado o categoría | caliente, templado, frio |
| VERSION_PUNTUACION | Texto | v1-acordada-2026-11-21 |
| SIGUIENTE_PASO_IA | Texto | Sugerencia revisable, nunca ejecutada automáticamente |
| ESTADO_IA | Texto | pending o complete |

Los campos heredados **IA_PUNTUACION, IA_CATEGORIA e IA_POTENCIAL no se escriben**: la IA no valora ni puntúa. No eliminarlos automáticamente de la cuenta; revisar antes automatizaciones y valores antiguos. RESUMEN_IA, si ya existe, recibe el mismo resumen que IA_NECESIDADES para compatibilidad.

PRESUPUESTO_ESTIMADO queda sin rellenar: hay un rango declarado, no una cifra estimada por IA. FECHA_PROPUESTA, IMPORTE_SETUP, IMPORTE_MENSUAL, CONTRATO_FIRMADO y ENLACE_PAGO quedan sin modificar; no se han implementado propuestas, contratos ni cobros.

### Atribución y campos técnicos opcionales

Crear **GCLID**, **GBRAID**, **WBRAID**, **UTM_SOURCE**, **UTM_MEDIUM**, **UTM_CONTENT**, **UTM_TERM** como Texto si se quieren sincronizar; la BD privada los conserva aunque no existan en Brevo. Igualmente, opcionales de texto: SERVICIO (clave exacta), SECTOR, OBJETIVO, PRESUPUESTO, URGENCIA, ID_SOLICITUD, VERSION_PRIVACIDAD. Estos conservan la información del cuestionario y trazabilidad. Una nueva solicitud no vacía valores opcionales ya existentes.

Google Ads se infiere por gclid/gbraid/wbraid o utm_source=google junto con medio cpc/ppc/paid/paidsearch. Google sin medio de pago se considera Web; no se confunde una visita orgánica con Ads. Los parámetros son atribución declarada, no prueba autenticada de un clic publicitario. LANDING guarda la ruta actual verificada contra entradas PHP existentes; no se sigue a la persona ni se conserva query/fragmento. No representa necesariamente la primera página de toda su navegación.

### Comprobación y conservación de estados

El worker lee `/contacts/attributes` una vez por proceso/transporte y resuelve las categorías con la enumeración real (incluido valueStr). Verifica tipos esenciales y exige los atributos de captación y valoración anteriores. Si falta alguno o no hay opción compatible, el lead queda guardado localmente y pendiente de sincronizar: no se marca una falsa sincronización. Revisar logs privados y configurar el atributo/opción; el worker lo reintentará. La caída de esta consulta no pierde el lead ni impide los emails.

Consulta el contacto existente antes de sincronizar: conserva ESTADO_LEAD ya informado, incluso al completar posteriormente la IA. No se reinicia una propuesta/contrato a Nuevo. Brevo representa la solicitud más reciente de un email, mientras SQLite conserva el historial. Vacíos opcionales no borran datos anteriores, por lo que un campo de contacto puede corresponder a una solicitud previa; ID_SOLICITUD y ESTADO_IA ayudan a distinguirlo si se crean.

Dar de alta/verificar remitente y dominio transaccional según las instrucciones DNS de la cuenta. Aviso y respuesta a ads@soto-digital.com; ai@soto-digital.com no recibe copia automática. **Actualización del 21/11/2026:** cuenta real consultada y cinco atributos requeridos creados con autorización. Lista válida, remitente activo y dominio autenticado; `brevo-check` devuelve ok=true. Prueba real posterior autorizada: contacto técnico nuevo, score PHP 9/10 caliente y dos emails entregados al buzón propio; ver `docs/prueba-real-brevo.md`. No se sobrescribieron contactos existentes. Brevo denomina `float` al atributo numérico por API: el código acepta `float` y `number`, manteniendo puntuación PHP entera 0–10.

Fuentes comprobadas: [atributos de Brevo](https://developers.brevo.com/reference/get-attributes), [crear/actualizar contacto](https://developers.brevo.com/reference/create-contact), [cambio valueStr en enumeraciones](https://developers.brevo.com/changelog/2026/5/1).

## Puntuación acordada

Versión `v1-acordada-2026-11-21`. PHP calcula y guarda la puntuación; los históricos conservan su versión y puntuación. Los tramos son cualificación, no precios.

| Dato | Puntos |
| --- | --- |
| Inversión ≥ 1.000 € / 300–1.000 € / < 300 € / por definir | 3 / 2 / 1 / 0 |
| Consultas, ventas o visibilidad / necesita orientación | 2 / 0 |
| Ahora o este mes / 1–3 meses / explorando | 3 / 1 / 0 |
| Web indicada | 1 |
| Móvil indicado | 1 |

Máximo 10. Caliente 7–10, templado 4–6, frio 0–3. Revisar siempre por una persona; no hay rechazo por potencial. Valores `PRESUPUESTO` de texto en Brevo: `menos-300`, `300-1000`, `1000-3000`, `mas-3000`, `por-definir`. El punto de corte de 1.000 € corresponde al tramo `1000-3000`.

## Claude

**Validado en local:** acceso real a Haiku 5.5, prueba sintética y dos solicitudes de prueba con resumen/siguiente paso completos en Brevo. Se usan 1200 tokens como máximo por petición y effort=low para ese modelo; se rechazan respuestas marcadas como truncadas o refusal. Ver `docs/prueba-real-claude.md`. Pendiente confirmar límite mensual en la cuenta y revisión humana de calidad.

Solo redacta resumen y siguiente paso: no decide ni modifica categoría. Se envían sector, objetivo, inversión, urgencia, existencia de web, servicio y puntuación. No se mandan nombre, email, móvil, URL, texto libre, identificadores de campaña ni IP.

La respuesta debe ser JSON con `resumen` y `siguiente_paso`: una frase de resumen (máximo 240 caracteres) y una acción concreta (máximo 180). No repite puntuación ni metodología. Los plazos describen cuándo quiere empezar el cliente, no resultados garantizados. Se rechaza una respuesta demasiado larga en vez de cortar frases. Tres intentos máximos por solicitud. Si falla o no hay clave, el lead y su puntuación siguen guardados, Brevo puede recibirlo sin resumen y el email informa «No disponible». Una recuperación posterior puede actualizar el resumen en Brevo sin reenviar emails ya entregados. Para reactivar una IA agotada es necesaria revisión técnica; no hay botón público.

## Cola y emails

Configurar cron cada cinco minutos (o la frecuencia elegida) con PHP CLI y el mismo SOTO_CONFIG_FILE/variables que PHP web:

```sh
php bin/campaign-leads.php retry
php bin/campaign-leads.php status
php bin/campaign-leads.php export
```

`retry` tiene bloqueo de fichero para evitar dos trabajadores simultáneos; procesa hasta 50 pendientes por ejecución, según los proveedores configurados, rotando los intentos para que fallos antiguos no bloqueen solicitudes nuevas. `status` muestra recuentos, sin contactos. `export` escribe CSV en stdout: solo guardar en un destino privado. No existe panel público ni endpoint de exportación.

Los emails salen por Brevo, no mail() ni Resend. Hay una clave de idempotencia determinista en cada mensaje. Los envíos con respuesta ambigua (timeout, error de servidor o proceso interrumpido) quedan con estado `sending`: revisar el panel de Brevo antes de reponerlos en `pending`, para evitar duplicados fuera de la ventana de deduplicación del proveedor. Fallos inequívocos permanecen pendientes. Esta revisión y la reparación de errores de cuenta/atributos requieren una persona; la tarea no puede arreglar credenciales ni DNS.

## Antispam y fallos

- CSRF y token de solicitud de sesión; reenviar la misma solicitud aceptada devuelve la misma referencia.
- Límite persistente: cinco intentos por conexión/IP en una ventana de una hora, con HMAC y sin IP en claro en esa tabla. Incluye intentos inválidos después de validar la sesión.
- `CAMPAIGN_TRUSTED_PROXIES` es una lista CIDR vacía por defecto. Solo para conexiones desde esos proxies se utiliza CF-Connecting-IP o el primer X-Forwarded-For válido. El proxy debe sustituir las cabeceras del visitante. Mantener la lista vigente: no confiar globalmente en todas las IP.
- Turnstile se comprueba en servidor: acción proposal y hostname autorizado.
- Campo trampa, nombres sin letras y dominios temporales conocidos se descartan sin guardar el contacto ni generar conversión. El listado local no detecta todos los dominios temporales.
- El éxito silencioso usa la misma respuesta pública y pantalla de agradecimiento, pero no emite un recibo de conversión real.
- Ningún dato personal se incluye en logs de errores del código. Revisar aparte logs del servidor y del proveedor.

## Consentimiento y medición

Consent Mode v2 básico, con las cuatro señales denegadas al inicio; la librería de Google no se carga hasta aceptar una categoría configurada. Analíticas concede analytics_storage; Publicitarias concede ad_storage y ad_user_data. ad_personalization permanece denegado. La nueva versión 3 de preferencias pide una elección nueva a preferencias antiguas.

GA4 recibe `generate_lead` solo en gracias con un recibo de una solicitud realmente guardada; Google Ads recibe su conversión directa si existe ID/label y permiso publicitario. El marcador se consume en el servidor y hay deduplicación por sesión y transaction_id. No se emite conversión por clic en Enviar ni por spam descartado. Google no recibe el formulario ni la puntuación. La captación es exclusivamente por formulario; se ha eliminado el código de WhatsApp.

Elegir una fuente de conversión principal: no importar además generate_lead como conversión principal si ya se utiliza la etiqueta directa. Verificar con Tag Assistant y con consentimiento aceptado, rechazado y retirado. En modo básico es normal no medir todas las solicitudes sin permiso. Medir leads reales también desde SQLite/Brevo.

La atribución se mantiene durante 30 minutos en la sesión para navegar entre landings sin perder el origen; no se inventa una campaña si no se recibió. Validar clics reales y redirecciones en producción.

## Pendientes de activación

Cuentas y claves, lista/atributos Brevo, remitente/DNS, almacenamiento privado, cron, IDs/labels Google, contratos y privacidad definitiva, revisión visual del equipo y prueba real controlada en el hosting. También alcance de la mini-auditoría, vídeos y testimonios auténticos. Todo ello se detalla en `pendientes.md`.

Fuentes de implementación:

- [API de contactos Brevo](https://developers.brevo.com/reference/create-contact).
- [Email transaccional Brevo](https://developers.brevo.com/reference/send-transac-email).
- [Modelos de Claude](https://platform.claude.com/docs/en/models/overview).
- [Consent Mode de Google](https://developers.google.com/tag-platform/security/guides/consent).

## Tokens, carga y atribución

HTML inicial sin sesión ni Set-Cookie, con `no-cache`. `api/token.php` se solicita por POST del mismo origen con `X-Soto-Form: 1` cuando el formulario se aproxima o recibe foco: devuelve tokens y sesión privada `no-store`. Turnstile se carga entonces y se renderiza en el paso de contacto. El envío conserva CSRF, firma de atribución, idempotencia y validación de servidor. Variantes cerradas `?v=cantabria` y `?v=empresas`, nunca texto libre en titulares. La atribución de sesión caduca en 30 minutos; la persistida se elimina con el lead.

Eventos analíticos: `lead_form_start`, `lead_form_step` (step 1–5), `lead_form_error` (tipo enumerado), `generate_lead`. Solo con consentimiento analítico, sin contenido de campos ni puntuación. Marcar `generate_lead` como evento clave; los pasos sirven para analizar abandonos. Las preferencias caducan a los 180 días. Dealfront requiere su propia activación y consentimiento; no obtiene el contacto personal de cada visitante.

## Operación, alertas y conservación

Configurar cron con rutas absolutas de **ese hosting** y `SOTO_CONFIG_FILE` privado. No copiar las rutas del ordenador de desarrollo.

| Frecuencia | Comando desde la raíz privada del proyecto |
| --- | --- |
| Cada 5 minutos | `php bin/campaign-leads.php retry` |
| Cada 5 minutos, monitor independiente | `php bin/campaign-leads.php health` |
| Diaria, antes de purgar | `php bin/campaign-leads.php backup` |
| Diaria, tras validar selección | `php bin/campaign-leads.php purge` |
| Semanal | `php bin/campaign-leads.php report --week` |

`health` devuelve comprobaciones sin llamar a proveedores: configuración, escritura SQLite, cola de más de 30 minutos y heartbeat worker de más de 15 minutos. `api/health.php` requiere `Authorization: Bearer <CAMPAIGN_HEALTH_TOKEN>` y devuelve solo ok/fail, sin datos. Configurar en un monitor que permita cabecera secreta, sin token en URL ni logs. Sin token configurado responde 404. Las alertas del worker se envían a `CAMPAIGN_ALERT_TO` (ads por defecto), limitadas a una por hora cuando la BD está disponible. **Un worker parado no puede alertar de sí mismo, ni Brevo enviar un aviso si Brevo falla**: el monitor externo debe avisar por una vía independiente. Esto sigue pendiente de alta, no está resuelto solo por código.

Copias consistentes `VACUUM INTO`, permisos 0600, carpeta privada `backups`, 14 copias por defecto (`CAMPAIGN_BACKUP_KEEP`). Configurar una copia diaria; ejecutar más veces acorta el historial en días. No son copias cifradas: el equipo debe decidir cifrado y copia externa. Restauración: detener cron y escrituras web, conservar BD actual, verificar copia con `PRAGMA integrity_check`, sustituir BD y retirar WAL/SHM solo con conexiones detenidas, ajustar propietario/permisos, probar y reanudar. No recuperar una copia vieja sin reaplicar eliminaciones.

`purge --dry-run` revisa recuentos; `purge` elimina fila, payload, IA y atribución tras 12 meses desde última comunicación (creación si no existe). `contact ID --commercial-active` registra comunicación y protege relación comercial; `contact ID` registra comunicación y libera esa protección. Mantener ese estado también al cerrar clientes: no hay sincronización de fechas ni contratos desde Brevo. Los backups conservan datos hasta rotación (14 copias) y requieren procedimiento de supresión/restauración. `CAMPAIGN_PURGE_BREVO=false` por defecto: el borrado remoto elimina todo el contacto, revisar primero otras finalidades y bases jurídicas. Si existe otra solicitud local del mismo email no se borra el contacto compartido; un fallo remoto conserva la referencia local para revisión.

Consultar `docs/siguiente-etapa-crm.md` para CRM y diseño offline. No hay propuestas, firmas, pagos ni encargos a freelancers implementados.

## Comprobar Brevo antes de activar

Con SOTO_CONFIG_FILE apuntando al archivo privado, ejecutar `php bin/campaign-leads.php brevo-check`. Consulta únicamente cuenta, lista, atributos, remitente y dominio: no crea contactos, no manda emails y no abre SQLite. Devuelve checks y código de salida 0 si todo es compatible; no muestra claves ni respuestas completas del proveedor. Un resultado correcto no valida entregabilidad, automatizaciones de la cuenta ni cron: probar después con un lead propio y envío expresamente autorizado.

Referencia de las comprobaciones: [cuenta](https://developers.brevo.com/reference/get-account), [remitentes](https://developers.brevo.com/reference/get-senders), [dominio](https://developers.brevo.com/reference/get-domain-configuration).

## Comprobaciones y paquete local posteriores

Ver `docs/comprobaciones-locales-brevo.md`: atributos opcionales completados, mensajes HTML revisados, pruebas de errores/copia/restauración/conservación y entrega real de muestras. CAMPAIGN_REQUIRE_AI=false mantiene la IA opcional para el health, con aviso si falta clave; true la exige. El token de health se conserva solo en configuración privada. Para construir el paquete: `python3 tools/build-release.py`; salida dist (no pública, no desplegada). No se incluyen archivos no versionados ni configuración del Mac. La revisión de workflows se basa en la confirmación del equipo de que no hay activos; no hubo acceso al panel por el navegador.

## Proceso de atención aprobado por el equipo

Jónatan y Rebeca atenderán las solicitudes. Primera respuesta: máximo 2 días laborables, no plazo de entrega de una propuesta completa. La revisión gratuita parte de los datos que facilita el interesado; según las necesidades se contacta por llamada (si deja móvil) o propuesta por email. No se promete un documento de auditoría independiente. Formulario, gracias, textos de revisión y emails están alineados. `CAMPAIGN_RESPONSE_TIME` expresa la duración; si está ausente o vacío se usa el plazo aprobado. La regla para asignarse cada solicitud y evitar respuestas duplicadas sigue pendiente; no hay asignación automática implementada.
