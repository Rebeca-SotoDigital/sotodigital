# Pendientes de Soto Digital

Actualizado: **26 de noviembre de 2026**.

Este es el checklist vigente. **El equipo ha publicado en soto-digital.com por SFTP.** La preparación del formulario pasó de HTTP 503 a HTTP 200 tras el ajuste del cargador privado; el equipo confirma la página de gracias. El equipo confirma el circuito en producción: contactos en Brevo con puntuación y resumen de IA, ambos correos recibidos y `pending: 0` en cron-job.org.

**Criterio vigente:** autorizado trabajar sobre el dominio principal. La subida de código se está realizando manualmente por SFTP; no se ha activado publicidad. No sobrescribir bases de leads ni claves con versiones locales.

## Pendientes vigentes — 26/11/2026

Landings y subida de sitemap/robots completadas, según confirmación del equipo. Este resumen sustituye los estados históricos que aparecen más abajo.

- [ ] **Google Ads:** completar facturación, configurar la conversión y preparar palabras clave, anuncios, zonas y presupuesto.
- [ ] **Contenido:** grabar vídeos e incorporar testimonios de TecniRefinish y Cantabria Kebab, con permisos y resultados verificables.
- [ ] **Pantalla LED:** confirmar disponibilidad y condiciones.
- [ ] **Legal y privacidad:** completar datos definitivos de la sociedad y revisar proveedores, inventario de cookies y gestión de derechos.
- [x] **Revisión técnica:** cerrada el 26/11/2026 tras verificar en producción el `.htaccess`: web 200, rutas internas 404, APIs 401 sin credenciales, gzip, caché y cabeceras de protección correctas. Evidencia en `docs/verificacion-htaccess-produccion-2026-11-26.json`.
- [x] **Copias:** punto cerrado por confirmación del equipo el 26/11/2026: copia diaria generada a la hora programada. Código de retención máxima de tres subido por SFTP. La descarga periódica al Mac queda como mantenimiento; el contador de archivos conservados no se ha verificado de forma independiente.
- [x] **Mantenimiento:** organización cerrada por confirmación del equipo el 26/11/2026. Rebeca es responsable principal y Jónatan suplente; destinatarios acordados `info@soto-digital.com` y `rebeca@soto-digital.com`. El equipo confirma que los canales y avisos de cron-job.org ya estaban configurados.
- [ ] **SEO:** revisar Google Business Profile y decidir los primeros contenidos del blog.

## Atención de las solicitudes

- [x] Responsables de atender solicitudes: Jónatan y Rebeca, ambos confirmados por el equipo.
- [x] Asignación acordada: quien atienda primero selecciona su nombre en `RESPONSABLE_LEAD` antes de responder. Campo creado y verificado en Brevo con Jónatan y Rebeca; acceso compartido. La web conserva la asignación manual.
- [x] Facilitar instrucciones para mostrar `RESPONSABLE_LEAD` en la ficha de Brevo.
- [x] El equipo confirma que ve `RESPONSABLE_LEAD`. Asignación y conservación verificadas mediante API real en un contacto interno. Es coordinación manual, sin bloqueo de edición simultánea. Véase `docs/asignacion-leads.md`.
- [x] Primera respuesta en un máximo de **2 días laborables**, aprobada por el equipo. No implica entregar una propuesta completa en ese plazo.
- [x] Revisión inicial: examinar los datos aportados y, según las necesidades, llamar si hay móvil o enviar una propuesta por email. No se promete un informe independiente ni una llamada si no se facilita teléfono.
- [x] El proceso de revisión inicial y los textos de captación están preparados. La página de gracias muestra el plazo aprobado de primera respuesta.
- [x] Estados aprobados y configurados en `ESTADO_LEAD`: Nuevo → En revisión → Contactado → Propuesta enviada → Ganado / Perdido. Se conservan las opciones antiguas y sus IDs, sin reclasificar contactos.
- [x] Revisión de solicitudes pendientes a cargo de ambos, Jónatan y Rebeca. Consultar responsable y estado antes de contactar para coordinarse.
- [x] Recorrido real probado en el contacto interno 101: seis estados, ambos responsables y resincronización desde el código local en cada paso. Responsable, estado, puntuación e IA conservados; valores originales restaurados. Sin emails ni llamadas a IA.
- [ ] Si se desean recordatorios automáticos, definir canal y momento del aviso antes de configurarlos. Actualmente la revisión es manual por ambos.

### Siguiente paso recomendado, sin publicar

GA4 recibe actividad y `generate_lead`; el equipo ha comprobado aceptación, rechazo y retirada del consentimiento en local. El equipo confirma el 25/11/2026 que `generate_lead` está marcado como evento clave. Nuevo envío desde producción comprobado por capturas: una ocurrencia de `generate_lead`, contabilizada como evento clave, con seis vistas de Gracias en esa ventana. Mantener desactivada la medición mejorada de formularios. Google Ads queda pendiente del alta de facturación y de sus identificadores; la verificación definitiva del dominio se realizará antes de activar campañas. El recorrido comercial básico ya está configurado y probado. Los datos registrales se posponen hasta que finalice la constitución.


## 1. Completado y comprobado en local

### Brevo y captación

- [x] Clave API y lista válidas; IP de la conexión local autorizada.
- [x] Remitente `ads@soto-digital.com` activo y dominio verificado/autenticado, con ambos DKIM, código de Brevo y DMARC correctos.
- [x] Atributos obligatorios y opcionales creados; servicio, campaña y trazabilidad sincronizados en los contactos internos de prueba. No se inventan valores de anuncios ausentes.
- [x] Formulario de cinco pasos con empresa, web y móvil opcionales, información de privacidad, CSRF, antispam y almacenamiento SQLite privado.
- [x] Turnstile real configurado y probado en localhost. Un token inválido devuelve 422 y no guarda un lead.
- [x] Puntuación **0–10 calculada en PHP**, con versión `v1-acordada-2026-11-21`: caliente 7–10, templado 4–6, frío 0–3. Se conservan puntuaciones históricas.
- [x] Envío real desde el navegador: solicitud guardada y página de gracias comprobada.
- [x] Worker local de macOS activo cada cinco minutos; sincroniza contactos y procesa emails sin ejecución manual. Requiere el Mac despierto y la sesión abierta.
- [x] Aviso al equipo y confirmación al solicitante entregados según eventos de Brevo. Se corrigió la dirección de prueba `ia@` a `ai@soto-digital.com` tras confirmación del usuario.
- [x] Un contacto existente se actualiza por email; no se crea un duplicado ni se reinicia su estado comercial.
- [x] El equipo ha confirmado que no hay automatizaciones activas en Brevo. Esta comprobación se basa en su revisión del panel, no en una inspección independiente del navegador.
- [x] El código no suscribe a newsletters ni modifica los bloqueos de marketing.

### Pruebas, seguridad y mantenimiento

- [x] Sesión inválida/caducada, doble clic, repetición del envío, fallos de proveedores y almacenamiento comprobados con datos aislados. El duplicado conserva un único lead.
- [x] Copia real privada con permisos 0600 y restauración en archivo aislado: integridad correcta y registros coincidentes; sin sobrescribir la BD original.
- [x] Conservación probada: dry-run en la BD real con **0 elegibles y 0 borrados**. Casos antiguos, relación comercial y última comunicación verificados en una BD aislada.
- [x] Alerta de prueba entregada a `ads@soto-digital.com`; repetición inmediata suprimida.
- [x] Endpoint de salud probado: 401 sin token y 200/ok con token privado. La IA no configurada avisa, pero no bloquea la salud básica de Brevo.
- [x] Cabeceras, caché, bloqueo de archivos internos, pruebas automatizadas y recursos con hash verificados en local. No implica certificación de seguridad o accesibilidad completa.

### Emails y paquete de publicación

- [x] Emails HTML adaptables, textos revisados y datos escapados. La puntuación y el resumen interno no se muestran al solicitante.
- [x] Entrega del correo de prueba a `rebeca@soto-digital.com` y de la alerta al equipo confirmadas por Brevo.
- [x] ZIP de la versión revisada preparado en `dist/soto-digital-arsys.zip`, con SHA256, manifiesto, ejemplo privado sin claves e instrucciones.
- [x] Paquete verificado: sin claves reales, bases de leads, logs, documentación ni herramientas de desarrollo; incluye la CLI operativa protegida en `bin/`.
- [x] Carpeta `dist/` bloqueada por HTTP (404). **No se ha desplegado el ZIP.**

## 2. Revisión y operación en Arsys — publicación realizada

### Hosting y configuración

- [ ] Confirmar qué versión hay en producción y preservar cualquier cambio ajeno antes de sustituirla.
- [ ] Disponer de acceso al alojamiento y DNS sin publicar credenciales en el chat ni en el repositorio.
- [ ] Comprobar PHP compatible, `pdo_sqlite`, cURL, mbstring, sesiones y conexiones salientes a los proveedores.
- [ ] Verificar Apache, rewrite/headers, HTTPS y dominio definitivo; publicar en la raíz prevista.
- [ ] Hacer copia de la web/configuración actuales y preparar la recuperación de la versión anterior.
- [ ] Crear configuración y almacenamiento privados fuera de `public_html`, con permisos limitados a los usuarios necesarios.
- [ ] Definir `SOTO_CONFIG_FILE` tanto para PHP web como para PHP CLI/cron. Completar las rutas propias de Arsys, sin copiar rutas ni ACL del Mac.
- [x] IP de Arsys autorizada en Brevo el 25/11/2026. Los intentos bloqueados coincidían con las ejecuciones del servidor; tras autorizarla, el equipo confirma contactos y correos recibidos.
- [ ] Crear o configurar un widget Turnstile de producción que admita `soto-digital.com` y los hostnames utilizados. Las claves locales no garantizan protección del dominio publicado.

### Automatización y mantenimiento en el servidor

- [x] Sustituido el cron nativo, no disponible en el plan según soporte de Arsys, por cron-job.org: POST protegido cada cinco minutos a `api/worker.php`. Ejecución automática 200 observada en captura y cola `pending: 0` confirmada por el equipo.
- [ ] Programar copias diarias, definir ubicación privada/externa y rotación, y comprobar una restauración en el entorno definitivo.
- [ ] Revisar el resultado de `purge --dry-run` antes de programar la conservación. Mantener actualizados última comunicación y relación comercial; no borrar indiscriminadamente clientes activos.
- [ ] Resolver la conservación en Brevo y en las copias. El borrado remoto de contactos permanece desactivado por defecto y requiere revisar otras finalidades y contactos compartidos.
- [ ] Configurar el monitor externo del endpoint de salud con token en cabecera y aviso por una vía independiente del worker y Brevo.
- [ ] Definir responsables de revisar errores, rebotes, respuestas ambiguas, copias y crecimiento de logs. `sent` significa aceptado por la API, no necesariamente entregado.

### Despliegue y prueba final

- [ ] Regenerar el ZIP desde la revisión aprobada y revisar su manifiesto y exclusiones. El código de `whatsapp/` ya está versionado. Confirmar si debe incluirse en esta publicación y comprobar que su configuración privada queda fuera.
- [ ] Publicar juntos PHP, reglas, imágenes, recursos con hash y manifiesto, con cambio atómico o una ventana que evite versiones mezcladas.
- [x] Resuelto HTTP 503 de `api/token.php`; respuesta 200 comprobada directamente. Envío y página de gracias confirmados por el equipo.
- [ ] Configurar y comprobar monitor de `health` en producción; el éxito del worker y las entregas no sustituyen esa revisión. `brevo-check` CLI queda disponible si se obtiene acceso de ejecución.
- [ ] Enviar solicitudes de prueba con Turnstile desde la portada y desde una página de servicio. Verificar guardado, puntuación, confirmación, ejecución del worker y `ORIGEN` y `LANDING` en Brevo. La prueba local simulada ya verificó esas rutas para portada, Diseño web y Campañas.
- [x] Entrega de ambos emails en producción confirmada por el equipo el 25/11/2026.
- [ ] Revisar dirección de respuesta y lectura de emails en móvil/escritorio en producción.
- [ ] Comprobar duplicados, recarga de gracias, errores y ausencia de éxito falso cuando no se pueda guardar.
- [ ] Revisar 404 de rutas internas, caché, sesión, noindex de campañas/API y sitemap/robots. Configurar `SITE_ENV=staging` para pruebas y `SITE_ENV=production` para la raíz. `/mumito/` ya retirado; no queda pendiente revisar el antiguo sitemap provisional.
- [ ] Comprobar consentimiento y conversión única con las etiquetas reales antes de invertir en anuncios.
- [ ] Documentar el resultado y la autorización del equipo para lanzar publicidad. Tener listo el rollback sin perder leads nuevos ni recuperar datos suprimidos.

## 3. Pendientes de medición, privacidad y contenido

### Medición y revisión de recepción

- [x] GA4 `G-1C1JJRTFPW` guardado en la configuración privada local. Verificado en el HTML servido por XAMPP; pruebas de consentimiento, eventos y deduplicación correctas con transporte simulado (22/11/2026). No se ha añadido el contenedor GTM mostrado durante el alta.
- [ ] Obtener y configurar `GOOGLE_ADS_ID` (`AW-…`) y `GOOGLE_ADS_LEAD_LABEL` de la conversión de solicitud. Actualmente vacíos en la web local.
- [x] Recepción real en GA4 confirmada por captura del equipo el 22/11/2026: 1 usuario activo, `generate_lead` 1, `lead_form_start` 1, `lead_form_step` 5 y `page_view` 4. La página de gracias registra 2 vistas y el envío 1 evento en la ventana observada; no equivale a una auditoría completa de deduplicación.
- [x] Solicitud local del 22/11/2026 a las 08:58 (hora peninsular) comprobada: inicialmente en cola, procesada manualmente antes del siguiente intervalo de cinco minutos. Contacto Brevo 104 verificado por API (HTTP 200) y presente en la lista configurada; IA completa y ambos emails aceptados por Brevo. Esta comprobación no verifica entrega en buzón ni demuestra un fallo del worker.
- [x] Aceptación, rechazo y retirada del consentimiento comprobados por el equipo en Chrome local el 22/11/2026: Network filtrado por `google` muestra peticiones al aceptar analítica y queda vacío tras rechazar/retirar y recargar. Confirmación del usuario guiada por capturas; no inspección directa del agente ni validación con Tag Assistant.
- [x] Interacciones con formularios de medición mejorada desactivada en GA4, comprobada en captura del equipo; se indicó pulsar Guardar.
- [ ] Verificar con Tag Assistant en el dominio definitivo los estados de consentimiento y las etiquetas reales de Ads antes de campañas; la prueba actual corresponde a GA4 en local.
- [x] El equipo confirma el 25/11/2026 haber marcado la estrella de `generate_lead` como evento clave en GA4. Nuevo envío de producción comprobado en capturas del 25/11/2026 a las 19:58: `generate_lead` 1 y evento clave 1, con 6 vistas de Gracias en la misma ventana observada. No se atribuye retroactividad a eventos anteriores ni se generaliza esta prueba a todos los escenarios.
- [ ] Google Ads: alta pendiente de perfil de pagos/datos fiscales definitivos; el acceso al panel devuelve al asistente de facturación. No se ha publicado ninguna campaña ni configurado el ID/label.
- [ ] Elegir una sola fuente de conversión principal, evitando duplicar etiqueta directa e importación GA4.
- [ ] Separar tráfico interno y de pruebas antes de publicidad: pospuesto por el equipo durante el trabajo local. IP dinámica y varios equipos; sin reglas IP creadas. El filtro Internal Traffic permanece en Prueba, sin exclusión activa. No se ha implementado marcado automático de localhost.
- [ ] Revisar CSP en Report-Only con los proveedores reales antes de imponerla; activar HSTS solo después de verificar HTTPS estable.
- [ ] Opcional: comprobar recepción en buzones propios de Gmail/Outlook. La dirección facilitada para estas pruebas fue del dominio Soto Digital; no se ha ensayado otro proveedor de buzón.

### Privacidad definitiva

- [ ] **Pospuesto hasta finalizar la constitución:** aportar datos del Registro Mercantil y verificar titular, denominación y NIF definitivos antes de publicar. El usuario aclara el 21/11/2026 que la sociedad está en constitución; esta información sustituye la confirmación anterior de sociedad constituida. Los datos incorporados a los textos locales requieren validación definitiva.
- [ ] Revisar contratos, entidades, transferencias y condiciones de los proveedores realmente contratados.
- [ ] Completar inventario y duraciones de cookies con la instalación definitiva. La caducidad técnica de preferencias a 180 días ya está implementada.
- [ ] Documentar quién atiende derechos y quién mantiene fechas de contacto/relación comercial y supresiones, incluidas copias y restauraciones.
- [ ] Revisar y aprobar los textos definitivos antes de publicar. El formulario sigue sin dar de alta en newsletters; cualquier cambio de finalidad necesitará tratamiento separado.

### Oferta, equipo y pruebas sociales

- [x] Revisión de contenido de `/campanas/google-ads.php` (22/11/2026): beneficios, alcance, revisión inicial, proceso y proyección, preguntas específicas y CTA sin recarga. Sin casos publicitarios, vídeos, tarifas ni resultados inventados. Pruebas locales automatizadas y HTTP correctas. Ver `docs/revision-landing-google-ads.md`.
- [ ] Revisar visualmente y aprobar con el equipo la landing de Google Ads en móvil/escritorio; comprobar titulares de variantes con los futuros anuncios.
- [ ] Repetir la revisión de contenido específica en las demás landings; conservan de momento el proceso y preguntas generales.

- [x] Alcance de revisión inicial y primera respuesta definidos: revisar datos aportados, valorar necesidades y contactar por llamada o propuesta por email; máximo 2 días laborables para responder.
- [ ] Aprobar precios si se quieren publicar. No hay tarifas aprobadas; los rangos del formulario no son precios.
- [x] Plazo de primera respuesta definido en la configuración. `CAMPAIGN_RESPONSE_TIME` tiene un valor predeterminado de 2 días laborables.
- [x] La página de gracias muestra el plazo aprobado de **2 días laborables como máximo para la primera respuesta**, mediante `campaign_response_time()`.
- [ ] Confirmar estado operativo, disponibilidad y condiciones reales de la pantalla LED antes de vender publicidad. Mantener identificada cualquier recreación y no prometer audiencias sin datos.
- [x] Equipo actualizado: Rebeca figura primero como CEO y cofundadora, con su frase personal. Jónatan figura como CCO y cofundador. Los cargos se muestran en negrita y la frase personal en un párrafo aparte.
- [x] Retratos de Rebeca y Jónatan incorporados a la sección de equipo en WebP, con el mismo formato y alineación.
- [ ] Incorporar la frase personal de Jónatan.
- [x] Las seis fichas tienen textos de Reto, Qué hicimos y Resultado, logos en el Portfolio y capturas de escritorio y móvil. Las etiquetas Web y Redes disponibles enlazan desde cada ficha.
- [x] Incorporados los testimonios de Why Not, Soto Cars, Soto Wash y Soto Carrocerías.
- [ ] Incorporar los testimonios de TecniRefinish y Cantabria Kebab. Confirmar los permisos de publicación de marcas y testimonios y distinguir los entregables de cualquier resultado medido.
- [x] Configurar el número de WhatsApp en el botón y comprobar el enlace y su mensaje precargado en local.
- [ ] Grabar la entrevista de Google Ads y después los vídeos de los servicios anunciados. Entregar archivos optimizados, miniaturas, subtítulos y autorización; revisar antes de mostrar los bloques.

### SEO, campañas y accesibilidad

- [x] Portada enlazada a las siete páginas públicas de servicio. Campañas dispone de una página propia con formulario integrado. Las landings de anuncios mantienen navegación y URLs separadas.
- [x] Sitemap regenerado en local con 18 URL canónicas de producción, incluidas las seis fichas y Campañas. `robots.txt` referencia el sitemap de raíz y permite leer las directivas noindex.
- [x] `/mumito/` retirado según el equipo y HTTP 404 comprobado directamente el 25/11/2026. El sitemap provisional ya no aplica; no se bloquea su rastreo en robots para que pueda observarse la retirada.

- [ ] Aprobar `/google-ads.php`, `/meta-ads.php` y blog antes de retirar su estado de borrador/noindex y regenerar el sitemap. No retirar noindex de las landings de campañas ni de la API.
- [ ] Definir y revisar los primeros contenidos del blog, sin publicar información o resultados inventados.
- [ ] Verificar Google Business Profile de Maliaño y validar el esquema en la herramienta de resultados enriquecidos sobre las URLs publicadas.
- [ ] Investigar con el Planificador real: servicios iniciales, zonas, presupuesto, grupos por intención, negativas, anuncios y landing/variante coherente.
- [ ] Revisar teclado y tecnologías de apoyo reales. Resolver si se requiere una alternativa de envío sin JavaScript; Turnstile lo necesita actualmente.
- [ ] Repetir Lighthouse en Arsys y revisar la variación de rendimiento de la home detectada en auditoría. Las mediciones locales no son Core Web Vitals de visitantes.

## 4. Ampliaciones independientes de la integración básica de Brevo

- [x] **Claude en local:** clave privada aceptada y modelo claude-haiku-5-5 disponible. Prueba sintética y dos solicitudes internas completadas: resumen y siguiente paso sincronizados en Brevo, ESTADO_IA=complete y puntuación PHP intacta. Peticiones sin nombre, email, móvil, empresa, URL, texto libre, IP ni atribución. Ver `docs/prueba-real-claude.md`.
- [x] **Claude — límite mensual:** el usuario confirma haber fijado 100 USD/mes en el panel de límites de Anthropic el 21/11/2026. Confirmación del usuario; no se ha verificado por API el importe ni su alcance de organización/workspace. Es un tope de consumo, no un cargo mensual fijo.
- [x] **Claude — estilo breve:** ajuste solicitado completado y revisado en los contactos internos 101/103. Resumen de una frase (máximo 240 caracteres) y siguiente paso concreto (máximo 180), sin repetir puntuación ni metodología; inicio del cliente separado del plazo de resultados. El usuario confirma la revisión en Brevo.
- [ ] **Claude — seguimiento:** revisar calidad con nuevos casos y vigilar consumo y errores. Resumen de IA en Brevo desde Arsys confirmado por el equipo el 25/11/2026; los límites por petición no sustituyen el límite mensual del proveedor.
- [ ] **Dealfront/Leadfeeder:** cuenta/plan, código, contratos, consentimiento específico, filtros y cobertura real, si se decide activar. No identifica a cada persona visitante.
- [ ] **CRM comercial:** segmentos, estados definitivos, IDs del pipeline/etapas y prueba del esqueleto de tratos. Responsable manual y estados de contacto ya configurados y probados con API real. `BREVO_CREATE_DEALS` sigue desactivado por defecto.
- [ ] **Conversiones offline:** aprobar hito, valor real, permisos, formato e importación. Actualmente están diseñadas, no implementadas ni importadas.
- [ ] **Fases futuras:** propuestas asistidas con aprobación, firma, cobros y freelancers. No desarrolladas en esta fase.

## 5. Documentación y entregables

- [x] Git local conectado al repositorio GitHub de Soto Digital; cuenta Rebeca-SotoDigital autorizada, rama `main` y autoría con email `noreply`. Configuraciones privadas y `.env` excluidos; el código de WhatsApp está en GitHub y su configuración privada sigue excluida. La subida a GitHub no despliega la web en Arsys.

- [Siete comprobaciones locales de Brevo](docs/comprobaciones-locales-brevo.md).
- [Integración y atributos](docs/integracion-leads.md).
- [Asignación manual de solicitudes](docs/asignacion-leads.md).
- [Prueba real del recorrido comercial](docs/prueba-recorrido-comercial.json).
- [Prueba real de Brevo y emails](docs/prueba-real-brevo.md).
- [Claude: conexión real y estilo breve](docs/prueba-real-claude.md).
- [Formulario local y corrección del destinatario](docs/prueba-formulario-local.md).
- [Worker local](docs/worker-local.md).
- [DNS de Brevo verificados](docs/brevo-dns.md).
- [Publicación y recuperación](docs/publicar-rendimiento.md).
- [Informe de auditoría](docs/auditoria-2026-11.md).
- Paquete: `dist/soto-digital-arsys.zip`; comprobación: `.sha256`; archivos: `dist/manifest.json`; instrucciones: `dist/LEEME-ARSYS.md`. Se regenera con `python3 tools/build-release.py` cuando cambie el código que se publicará.

## 6. Historial

La planificación anterior se conserva en [el archivo histórico](docs/pendientes-historico-previo-a-consolidacion-2026-11-21.md). Contiene decisiones y casillas de etapas ya superadas; **no es el checklist vigente**. Esta consolidación elimina contradicciones sobre claves, tabla provisional, Turnstile, worker y pruebas reales, sin perder ese historial.


## Incidencia inicial en Arsys — 25/11/2026

- [x] El equipo confirma haber subido la web al dominio principal por SFTP.
- [x] Diagnóstico público: POST `/api/token.php` devuelve 503 y el HTML no incluye Turnstile; la preparación se detiene antes de Brevo. No atribuir este error a la API de Brevo.
- [x] Captura del equipo confirma `data/soto-private/soto-config.php`, carpeta `leads` y configuración de almacenamiento con `__DIR__ . '/leads'`.
- [x] Preparado y probado en local el cargador compatible con variables Apache/FastCGI, prefijos REDIRECT y ubicación privada fija de Arsys. Mantiene bloqueo de configuraciones dentro del directorio público y prioridad de variables explícitas. No contiene claves.
- [x] Cargador corregido subido por el equipo; endpoint de preparación HTTP 200 comprobado directamente.
- [x] Circuito de pruebas de producción completado según capturas y confirmación del equipo: envío aceptado, procesador externo activo, IP autorizada, contactos con puntuación/resumen de IA, ambos correos recibidos y cola sin pendientes. Esto no certifica todos los escenarios de fallo ni una auditoría completa de permisos.


## Procesamiento sin cron nativo — 25/11/2026

- [x] Implementado intento de CRM y correos tras guardar el formulario, con sesión liberada, bloqueo compartido y presupuesto temporal. La IA se completa después en cola.
- [x] Endpoint POST `api/worker.php` con clave privada independiente en cabecera, lotes limitados y sin datos personales en su respuesta. Pruebas de autenticación, concurrencia, presupuesto y no duplicación correctas con proveedores simulados.
- [x] Generada una línea de configuración privada fuera de Git y preparado ZIP de cinco archivos para subir por SFTP. Instrucciones en `docs/worker-http-arsys.md`.
- [x] Parche subido y token privado añadido por el equipo; la prueba externa autenticada devuelve HTTP 200. La clave no se versiona.
- [x] Cuenta y tarea cron-job.org creadas y habilitadas: POST cada cinco minutos con cabecera privada, cuerpo vacío y timeout de 30 segundos. Captura confirma ejecución automática.
- [x] Email de alerta de cron-job.org recibido en `info@soto-digital.com`, confirmado por captura del equipo el 25/11/2026 a las 18:47. Prueba mediante tarea temporal sin token: ejecución programada HTTP 401. Acredita el canal de notificaciones; no una caída de la tarea principal.
- [ ] Eliminar la tarea temporal «Prueba de alertas», manteniendo «Soto Digital — Procesar leads» habilitada.
- [ ] Confirmar que la tarea principal conserva los avisos de fallo/recuperación; la recepción del email se ensayó en la tarea temporal.
- [x] Monitor independiente Better Stack activo en `/api/health.php`, estado Up y frecuencia de tres minutos confirmados por captura del 25/11/2026 a las 19:06. El equipo confirma recepción de alerta de prueba. Detecta cola envejecida/proceso detenido según umbrales del endpoint; no se ha provocado una caída real del servicio.
- [x] Cola existente recuperada: el equipo confirma contactos con puntuación y resumen de IA, recepción de ambos correos y `pending: 0`. Bloqueo inicial de Brevo resuelto al autorizar la IP de Arsys.


## Monitor independiente de salud — preparación 25/11/2026

- [x] Endpoint existente `api/health.php` revisado: respuesta simple `ok`/200 si almacenamiento y configuración básica funcionan, heartbeat de worker de como máximo 15 minutos y sin solicitudes de CRM/correos pendientes de más de 30 minutos. No consulta en vivo los proveedores ni detecta todos los problemas de IA opcional.
- [x] Pruebas aisladas de proceso detenido, cola envejecida, base sin escritura y estados de configuración correctas. Clave de salud independiente generada fuera de Git y entregada en Descargas; no se muestra en el chat.
- [x] Monitor autenticado configurado por el equipo; captura posterior confirma Up en el endpoint protegido. La clave de salud se mantiene privada y separada de la del worker.
- [x] Cuenta Better Stack y monitor configurados por el equipo: URL `/api/health.php`, método GET y autenticación por cabecera; Up y comprobación cada tres minutos visibles en captura. Avisos por email y alerta manual recibida, confirmada por el equipo. No se ha comprobado una regla adicional de contenido `ok`; el estado Up acredita el chequeo HTTP configurado.
- [x] Monitor de salud autenticado Up y recepción de alerta manual de Better Stack confirmados.
- [ ] Opcional: comprobar incidente y recuperación reales en un monitor temporal con credencial incorrecta, sin tocar el monitor ni el worker principales. La alerta manual no acredita ese ciclo completo.


## Copias y restauración en producción — preparación 25/11/2026

- [x] Preparado endpoint POST protegido `api/backup.php`, con la misma cabecera privada del worker. No descarga bases ni permite restaurar sobre la base activa.
- [x] Implementada copia consistente en almacenamiento privado `leads/backups`, prueba de restauración en archivo aislado, integridad SQLite, checksum y conteos. Limpieza del archivo de prueba y rotación solo tras validar la copia nueva (14 por defecto, configurable 2–90).
- [x] Pruebas locales con datos sintéticos: copia/restauración, bloqueo, límite de repetición, permisos, rotación, rechazo de copia corrupta y conservación de leads originales. No se ha copiado todavía la base de producción mediante este endpoint.
- [x] Parche subido y copia real de Arsys ejecutada: HTTP 200, `integrity: ok`, `restore_test: ok`, 5 leads y retención de 14 copias. Archivo `leads-20261125-183854-21650808.sqlite`; informe y SHA256 en `docs/prueba-backup-produccion.json`. Restauración en archivo aislado, sin sustituir la base activa.
- [x] Tarea diaria de backup creada en cron-job.org con POST y cabecera privada; prueba desde cron-job.org HTTP 200 y `ok: true`, confirmada por captura.
- [ ] Confirmar guardado de la hora diaria fuera del minuto del worker (recomendada 03:32), aviso de recuperación y primera ejecución diaria en el historial.
- [ ] Descargar y custodiar una copia por SFTP fuera del servidor. Las copias en el mismo hosting no cubren pérdida de toda la cuenta; mantener acceso restringido y aplicar conservación/supresiones también a copias externas.

- Prueba inicial real de backup del 25/11/2026: endpoint presente/protegido; POST autenticado devuelve 503. No se da por creada/verificada la copia de Arsys. Preparado diagnóstico autenticado por fase y motivo sin rutas ni datos; pendiente subir el parche actualizado y repetir.

- Diagnóstico de Arsys confirmado: fase `snapshot`, motivo `sqlite_vacuum_into_unsupported`. Implementada y probada alternativa transaccional compatible con SQLite antiguo; pendiente subir `includes/campaign-operations.php` y repetir la copia de producción.

- Cierre de incidencia de backup: tras subir la alternativa compatible, copia real y restauración aislada correctas el 25/11/2026. Siguen pendientes la programación diaria y la descarga/custodia externa por SFTP.

- Custodia externa acordada: descarga manual por SFTP a una carpeta privada del Mac, semanal y antes de cambios importantes. No está automatizada y todavía no se ha confirmado una descarga.
- [ ] Renovar el token del worker mostrado en una captura y actualizarlo en la configuración privada de Arsys y las tareas de procesamiento/backup. No se incluye su valor en Git.

- [x] Medición GA4 en producción verificada con el equipo el 25/11/2026 a las 19:58: 1 usuario activo, 8 page_view, 6 vistas de Gracias, 1 generate_lead y 1 evento clave generate_lead. La prueba observada no muestra duplicación del lead al recargar Gracias.

- [x] Consentimiento en producción comprobado por el equipo en Chrome el 25/11/2026: con rechazo no aparecen peticiones de Google en Network; al aceptar analítica aparecen; tras retirar el consentimiento, limpiar la lista y recargar dejan de aparecer. Confirmación del usuario; no inspección directa del agente ni validación completa con Tag Assistant/Ads.

- [x] `sitemap.xml` regenerado el 25/11/2026: 18 URL canónicas de raíz, sin `/mumito/` ni endpoints/campañas noindex. `robots.txt` actualizado para el estado de producción y referencia al sitemap de raíz. El equipo confirma que ambos archivos ya están subidos a Arsys por SFTP.

## Revisión de landings en producción — 25/11/2026

- [x] Primera revisión HTTP de las once landings de `/campanas/`: páginas y diez recursos compartidos disponibles, un H1 y un formulario por landing, endpoint `/api/lead.php`, anclas internas correctas y noindex en HTML/cabeceras. Informe `docs/revision-landings-produccion.json`. No se han enviado formularios adicionales.
- [x] Revisión de contenido y diseño de landings en móvil y escritorio completada, según confirmación del equipo el 26/11/2026.
- Observación de contenido: Google Ads tiene alcance y preguntas específicos; las otras landings conservan FAQs y proceso generales. Sus beneficios definidos no se muestran en el bloque inicial como los de Google Ads. Revisar cada servicio antes de dar la aprobación comercial. Vídeos y casos publicitarios requieren material verificable.


## Quince mejoras de home y landings — 25/11/2026

- [x] Aplicadas en local las mejoras acordadas: titular y hero Google Ads, beneficios, CTA, espacios, retirada de ejemplo, prioridad del formulario, multicanalidad, formulario temprano y portfolio abreviado de home, enlaces de servicios, categorías de proyectos, primera respuesta y alcance/FAQ por servicio.
- [x] Cuestionario completo comprobado con pruebas funcionales, incluidos errores y contacto; ajuste Turnstile compacto en ancho reducido y campos de texto de 16 px. No se ha realizado una nueva verificación visual de pasos 2–5 en un dispositivo real.
- [x] Recursos con hash y sitemap regenerados. Suite de pruebas correcta y comprobación de estructura/render de las once landings. Detalle en `docs/mejoras-visuales-landings.md`.
- [x] Revisión final de contenido y diseño en móvil/escritorio completada por el equipo, confirmada el 26/11/2026.

- Ajuste visual solicitado: recuperadas en local las proporciones anteriores (titulares, columnas y espaciado). Revisión visual completada por el equipo el 26/11/2026.

## Revisión técnica y retención — 26/11/2026

- [x] El equipo confirma generación automática de copias de leads.
- [x] Retención cambiada en código a un máximo de tres copias, incluso con configuración antigua de 14. Las antiguas se eliminan solo después de verificar la nueva; no se toca la base activa. Prueba aislada con configuración antigua, cinco copias y resultado de tres conservadas correcta.
- [ ] Subir `includes/campaign-backup.php` y comprobar `retention_limit: 3`. La limpieza de copias antiguas se aplicará en la siguiente ejecución correcta, no desde este entorno local.
- [x] Suite local completa correcta: copias/restauración, formularios, consentimiento, rutas, recursos y sintaxis PHP.
- [x] Revisión HTTP ampliada a 29 páginas y 38 recursos/enlaces: respuestas 200, un H1 por página, imágenes con alt y anclas internas correctas.
- [ ] Producción: aplicar `.htaccess`. Actualmente includes/docs/tools/dist responden 200 y faltan cabeceras globales de protección, caché y compresión. API worker/salud rechazan acceso sin token (401); `.git` devuelve 404. No dar por cerrada esta incidencia hasta repetir comprobaciones tras subir el archivo oculto.
- [x] Robots revisado y sitemap regenerado: 18 páginas públicas, sin campañas noindex ni API. Subir ambas versiones del paquete.
- Rendimiento/accesibilidad: comprobación de recursos y estructura realizada; las mediciones Lighthouse anteriores son locales, no una medición nueva de Core Web Vitals en producción ni una certificación de accesibilidad.

- 26/11/2026: parche colocado directamente en el proyecto local (no requiere descargar ni descomprimir): `.htaccess`, `includes/campaign-backup.php`, `robots.txt` y `sitemap.xml`. Retención probada con configuración antigua de 14: máximo tres copias. Falta subir estos originales por SFTP y comprobar el efecto en Arsys.

- [x] Punto 6 cerrado a petición del equipo el 26/11/2026: confirmada la ejecución de la copia de hoy a la hora estipulada. La descarga periódica externa se mantiene como tarea recurrente de mantenimiento.

- [x] Punto 5 cerrado el 26/11/2026: comprobación HTTP posterior a la subida del `.htaccess` correcta (12 rutas). Sustituye el estado de incidencia anterior; incluye home/Google Ads, bloqueos de includes/docs/tools/dist/.git, caché immutable en CSS con hash, gzip y cabeceras nosniff/SAMEORIGIN. Robots y sitemap disponibles. No implica certificación completa de accesibilidad ni nuevas métricas Core Web Vitals.

## Organización del mantenimiento — 26/11/2026

- [x] Responsable principal: Rebeca. Suplente y seguimiento: Jónatan mediante info@.
- [x] El equipo confirma configurados los dos destinatarios y avisos de cron-job.org para procesamiento y copias. Confirmación del equipo, sin inspección nueva del panel.
- Destinatarios acordados para alertas: `info@soto-digital.com` y `rebeca@soto-digital.com`. La confirmación de esta conversación cubre cron-job.org; no acredita un cambio nuevo en Better Stack ni en los avisos internos de la web.
- Rutina recurrente: revisar alertas y solicitudes cada día laborable; errores, rebotes, ejecuciones y descarga de copia al Mac cada semana y antes de cambios importantes; consumo/facturación de IA y Brevo cada mes. Quien atienda un aviso informa al otro y comprueba la recuperación. Estas tareas continúan aunque la organización se da por completada.
