# Pendientes de Soto Digital

Actualizado: **25 de noviembre de 2026**.

Este es el checklist vigente. **El formulario y Brevo se verificaron en local.** La última comprobación de la versión provisional en `/mumito/` devolvió HTTP 503 en `api/token.php` tanto para la portada como para Diseño web. La publicación en la raíz y la prueba real desde Arsys siguen pendientes.

**Criterio vigente del equipo:** continuar en local hasta que todo esté preparado y revisado. No publicar ni modificar Arsys sin autorización posterior. El apartado de producción es preparación para esa fase, no una orden de despliegue.

## Atención de las solicitudes

- [x] Responsables de atender solicitudes: Jónatan y Rebeca, ambos confirmados por el equipo.
- [x] Asignación acordada: quien atienda primero selecciona su nombre en `RESPONSABLE_LEAD` antes de responder. Campo creado y verificado en Brevo con Jónatan y Rebeca; acceso compartido. La web conserva la asignación manual.
- [x] Facilitar instrucciones para mostrar `RESPONSABLE_LEAD` en la ficha de Brevo.
- [x] El equipo confirma que ve `RESPONSABLE_LEAD`. Asignación y conservación verificadas mediante API real en un contacto interno. Es coordinación manual, sin bloqueo de edición simultánea. Véase `docs/asignacion-leads.md`.
- [x] Primera respuesta en un máximo de **2 días laborables**, aprobada por el equipo. No implica entregar una propuesta completa en ese plazo.
- [x] Revisión inicial: examinar los datos aportados y, según las necesidades, llamar si hay móvil o enviar una propuesta por email. No se promete un informe independiente ni una llamada si no se facilita teléfono.
- [x] El proceso de revisión inicial y los textos de captación están preparados. El plazo ya aprobado queda por incorporar en la página de gracias.
- [x] Estados aprobados y configurados en `ESTADO_LEAD`: Nuevo → En revisión → Contactado → Propuesta enviada → Ganado / Perdido. Se conservan las opciones antiguas y sus IDs, sin reclasificar contactos.
- [x] Revisión de solicitudes pendientes a cargo de ambos, Jónatan y Rebeca. Consultar responsable y estado antes de contactar para coordinarse.
- [x] Recorrido real probado en el contacto interno 101: seis estados, ambos responsables y resincronización desde el código local en cada paso. Responsable, estado, puntuación e IA conservados; valores originales restaurados. Sin emails ni llamadas a IA.
- [ ] Si se desean recordatorios automáticos, definir canal y momento del aviso antes de configurarlos. Actualmente la revisión es manual por ambos.

### Siguiente paso recomendado, sin publicar

GA4 recibe actividad y `generate_lead`; el equipo ha comprobado aceptación, rechazo y retirada del consentimiento en local. Siguiente paso: marcar `generate_lead` como evento clave cuando aparezca en Eventos recientes (la interfaz indica hasta 24 horas), sin crear un evento duplicado. Confirmar también que la medición mejorada de formularios está desactivada. Google Ads queda pendiente del alta de facturación y de sus identificadores; la verificación definitiva del dominio se realizará antes de activar campañas. El recorrido comercial básico ya está configurado y probado. Los datos registrales se posponen hasta que finalice la constitución.


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

## 2. Antes de publicar en Arsys — prioritario

### Hosting y configuración

- [ ] Confirmar qué versión hay en producción y preservar cualquier cambio ajeno antes de sustituirla.
- [ ] Disponer de acceso al alojamiento y DNS sin publicar credenciales en el chat ni en el repositorio.
- [ ] Comprobar PHP compatible, `pdo_sqlite`, cURL, mbstring, sesiones y conexiones salientes a los proveedores.
- [ ] Verificar Apache, rewrite/headers, HTTPS y dominio definitivo; publicar en la raíz prevista.
- [ ] Hacer copia de la web/configuración actuales y preparar la recuperación de la versión anterior.
- [ ] Crear configuración y almacenamiento privados fuera de `public_html`, con permisos limitados a los usuarios necesarios.
- [ ] Definir `SOTO_CONFIG_FILE` tanto para PHP web como para PHP CLI/cron. Completar las rutas propias de Arsys, sin copiar rutas ni ACL del Mac.
- [ ] Autorizar **la IP saliente de Arsys** en Brevo. La IP autorizada en la prueba local no cubre automáticamente el servidor.
- [ ] Crear o configurar un widget Turnstile de producción que admita `soto-digital.com` y los hostnames utilizados. Las claves locales no garantizan protección del dominio publicado.

### Automatización y mantenimiento en el servidor

- [ ] Programar `php bin/campaign-leads.php retry` **cada cinco minutos** con el PHP y las rutas de Arsys. El LaunchAgent del Mac no sustituye este cron.
- [ ] Programar copias diarias, definir ubicación privada/externa y rotación, y comprobar una restauración en el entorno definitivo.
- [ ] Revisar el resultado de `purge --dry-run` antes de programar la conservación. Mantener actualizados última comunicación y relación comercial; no borrar indiscriminadamente clientes activos.
- [ ] Resolver la conservación en Brevo y en las copias. El borrado remoto de contactos permanece desactivado por defecto y requiere revisar otras finalidades y contactos compartidos.
- [ ] Configurar el monitor externo del endpoint de salud con token en cabecera y aviso por una vía independiente del worker y Brevo.
- [ ] Definir responsables de revisar errores, rebotes, respuestas ambiguas, copias y crecimiento de logs. `sent` significa aceptado por la API, no necesariamente entregado.

### Despliegue y prueba final

- [ ] Regenerar el ZIP desde la revisión aprobada y revisar su manifiesto y exclusiones. El código de `whatsapp/` ya está versionado. Confirmar si debe incluirse en esta publicación y comprobar que su configuración privada queda fuera.
- [ ] Publicar juntos PHP, reglas, imágenes, recursos con hash y manifiesto, con cambio atómico o una ventana que evite versiones mezcladas.
- [ ] Ejecutar `brevo-check` y `health` en Arsys. Resolver el HTTP 503 de `api/token.php` confirmando que PHP web recibe `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` y `CAMPAIGN_DATA_DIR`, y que el almacenamiento privado es escribible.
- [ ] Enviar solicitudes de prueba con Turnstile desde la portada y desde una página de servicio. Verificar guardado, puntuación, confirmación, ejecución del worker y `ORIGEN` y `LANDING` en Brevo. La prueba local simulada ya verificó esas rutas para portada, Diseño web y Campañas.
- [ ] Comprobar entrega de **ambos** emails, dirección de respuesta y lectura en móvil/escritorio.
- [ ] Comprobar duplicados, recarga de gracias, errores y ausencia de éxito falso cuando no se pueda guardar.
- [ ] Revisar 404 de rutas internas, caché, sesión, noindex de campañas/API y sitemap/robots. Configurar `SITE_ENV=staging` para pruebas y `SITE_ENV=production` para la raíz. Tras validar producción, redirigir las visitas GET/HEAD de `/mumito/` a su ruta equivalente, conservando la consulta.
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
- [ ] Comprobar si `generate_lead` está marcado como evento clave en GA4 y marcarlo si falta.
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
- [ ] Sustituir el marcador del plazo en `campanas/gracias.php` por el máximo aprobado de **2 días laborables para la primera respuesta**. Diferenciar ese plazo de la entrega de la propuesta.
- [ ] Confirmar estado operativo, disponibilidad y condiciones reales de la pantalla LED antes de vender publicidad. Mantener identificada cualquier recreación y no prometer audiencias sin datos.
- [x] Equipo actualizado: Rebeca figura primero como CEO y cofundadora, con su frase personal. Jónatan figura como CCO y cofundador. Los cargos se muestran en negrita y la frase personal en un párrafo aparte.
- [x] Retratos de Rebeca y Jónatan incorporados a la sección de equipo en WebP, con el mismo formato y alineación.
- [ ] Incorporar la frase personal de Jónatan.
- [x] Las seis fichas tienen textos de Reto, Qué hicimos y Resultado, logos en el Portfolio y capturas de escritorio y móvil. Las etiquetas Web y Redes disponibles enlazan desde cada ficha.
- [x] Incorporados los testimonios de Why Not, Soto Cars, Soto Wash y Soto Carrocerías.
- [ ] Incorporar los testimonios de TecniRefinish y Cantabria Kebab. Confirmar los permisos de publicación de marcas y testimonios y distinguir los entregables de cualquier resultado medido.
- [ ] Facilitar el número público de WhatsApp y comprobar el botón y su mensaje precargado.
- [ ] Grabar la entrevista de Google Ads y después los vídeos de los servicios anunciados. Entregar archivos optimizados, miniaturas, subtítulos y autorización; revisar antes de mostrar los bloques.

### SEO, campañas y accesibilidad

- [x] Portada enlazada a las siete páginas públicas de servicio. Campañas dispone de una página propia con formulario integrado. Las landings de anuncios mantienen navegación y URLs separadas.
- [x] Sitemap regenerado en local con 18 URL canónicas de producción, incluidas las seis fichas y Campañas. `robots.txt` referencia el sitemap de raíz y permite leer las directivas noindex.
- [ ] Verificar en Apache que el sitemap provisional bajo `/mumito/` entrega `X-Robots-Tag: noindex, nofollow`. En la última consulta a la versión publicada esa cabecera no estaba presente.

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
- [ ] **Claude — seguimiento:** revisar calidad con nuevos casos y vigilar consumo y errores. La prueba en Arsys queda para cuando se autorice publicar; los límites por petición no sustituyen el límite mensual del proveedor.
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
