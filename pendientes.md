# Pendientes de Soto Digital

Actualizado: **21 de noviembre de 2026**.

Este es el checklist vigente. **Brevo y el formulario funcionan en local; la web actualizada aún no se ha publicado en Arsys.** Las pruebas locales no sustituyen las comprobaciones en el servidor definitivo.

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

- [ ] Revisar el ZIP, su manifiesto y las exclusiones antes de subirlo. El trabajo independiente de WhatsApp sin versionar **no está incluido**; necesita revisión propia si se quiere incorporarlo.
- [ ] Publicar juntos PHP, reglas, imágenes, recursos con hash y manifiesto, con cambio atómico o una ventana que evite versiones mezcladas.
- [ ] Ejecutar `brevo-check` y `health` en Arsys.
- [ ] Enviar un formulario propio con Turnstile válido desde el dominio publicado y verificar guardado, puntuación, gracias, contacto/lista y ejecución automática del worker.
- [ ] Comprobar entrega de **ambos** emails, dirección de respuesta y lectura en móvil/escritorio.
- [ ] Comprobar duplicados, recarga de gracias, errores y ausencia de éxito falso cuando no se pueda guardar.
- [ ] Revisar 404 de rutas internas, caché, ausencia de sesión inicial innecesaria, noindex de campañas/API y sitemap/robots.
- [ ] Comprobar consentimiento y conversión única con las etiquetas reales antes de invertir en anuncios.
- [ ] Documentar el resultado y la autorización del equipo para lanzar publicidad. Tener listo el rollback sin perder leads nuevos ni recuperar datos suprimidos.

## 3. Pendientes de medición, privacidad y contenido

### Medición y revisión de recepción

- [ ] Configurar/verificar IDs GA4/Google Ads y label de formulario; comprobar con Tag Assistant aceptar, rechazar y retirar consentimiento.
- [ ] Elegir una sola fuente de conversión principal, evitando duplicar etiqueta directa e importación GA4.
- [ ] Excluir tráfico interno y de pruebas de los informes cuando corresponda.
- [ ] Revisar CSP en Report-Only con los proveedores reales antes de imponerla; activar HSTS solo después de verificar HTTPS estable.
- [ ] Opcional: comprobar recepción en buzones propios de Gmail/Outlook. La dirección facilitada para estas pruebas fue del dominio Soto Digital; no se ha ensayado otro proveedor de buzón.

### Privacidad definitiva

- [ ] Aportar datos del Registro Mercantil para completar el aviso legal. Razón social, NIF, domicilio y contacto ya están incorporados.
- [ ] Revisar contratos, entidades, transferencias y condiciones de los proveedores realmente contratados.
- [ ] Completar inventario y duraciones de cookies con la instalación definitiva. La caducidad técnica de preferencias a 180 días ya está implementada.
- [ ] Documentar quién atiende derechos y quién mantiene fechas de contacto/relación comercial y supresiones, incluidas copias y restauraciones.
- [ ] Revisar y aprobar los textos definitivos antes de publicar. El formulario sigue sin dar de alta en newsletters; cualquier cambio de finalidad necesitará tratamiento separado.

### Oferta, equipo y pruebas sociales

- [ ] Concretar alcance y entrega de la mini-auditoría gratuita, plazo real de respuesta y precios si se quieren publicar. No hay tarifas aprobadas; los rangos del formulario no son precios.
- [ ] Completar `CAMPAIGN_RESPONSE_TIME` solo con un plazo aprobado y sostenible.
- [ ] Confirmar estado operativo, disponibilidad y condiciones reales de la pantalla LED antes de vender publicidad. Mantener identificada cualquier recreación y no prometer audiencias sin datos.
- [ ] Entregar fotos autorizadas de Jónatan y Rebeca y aprobar su presentación.
- [ ] Reunir permisos de proyectos/logos, testimonios auténticos y resultados verificables. Distinguir entregables, resultados medidos y negocios propios.
- [ ] Grabar la entrevista de Google Ads y después los vídeos de los servicios anunciados. Entregar archivos optimizados, miniaturas, subtítulos y autorización; revisar antes de mostrar los bloques.

### SEO, campañas y accesibilidad

- [ ] Aprobar `/google-ads.php`, `/meta-ads.php` y blog antes de retirar su estado de borrador/noindex y regenerar el sitemap. No retirar noindex de las landings de campañas ni de la API.
- [ ] Definir y revisar los primeros contenidos del blog, sin publicar información o resultados inventados.
- [ ] Verificar Google Business Profile de Maliaño y validar el esquema en la herramienta de resultados enriquecidos sobre las URLs publicadas.
- [ ] Investigar con el Planificador real: servicios iniciales, zonas, presupuesto, grupos por intención, negativas, anuncios y landing/variante coherente.
- [ ] Revisar teclado y tecnologías de apoyo reales. Resolver si se requiere una alternativa de envío sin JavaScript; Turnstile lo necesita actualmente.
- [ ] Repetir Lighthouse en Arsys y revisar la variación de rendimiento de la home detectada en auditoría. Las mediciones locales no son Core Web Vitals de visitantes.

## 4. Ampliaciones independientes de la integración básica de Brevo

- [ ] **Claude:** cuenta API, clave privada, facturación/límite y modelo accesible; probar resumen y siguiente paso con revisión humana. La puntuación permanece en PHP y el modelo no recibe contacto ni texto libre. No bloquea Brevo mientras sea opcional.
- [ ] **Dealfront/Leadfeeder:** cuenta/plan, código, contratos, consentimiento específico, filtros y cobertura real, si se decide activar. No identifica a cada persona visitante.
- [ ] **CRM comercial:** segmentos, responsable, IDs del pipeline/etapas y prueba del esqueleto de tratos; `BREVO_CREATE_DEALS` sigue desactivado por defecto.
- [ ] **Conversiones offline:** aprobar hito, valor real, permisos, formato e importación. Actualmente están diseñadas, no implementadas ni importadas.
- [ ] **Fases futuras:** propuestas asistidas con aprobación, firma, cobros y freelancers. No desarrolladas en esta fase.

## 5. Documentación y entregables

- [Siete comprobaciones locales de Brevo](docs/comprobaciones-locales-brevo.md).
- [Integración y atributos](docs/integracion-leads.md).
- [Prueba real de Brevo y emails](docs/prueba-real-brevo.md).
- [Formulario local y corrección del destinatario](docs/prueba-formulario-local.md).
- [Worker local](docs/worker-local.md).
- [DNS de Brevo verificados](docs/brevo-dns.md).
- [Publicación y recuperación](docs/publicar-rendimiento.md).
- [Informe de auditoría](docs/auditoria-2026-11.md).
- Paquete: `dist/soto-digital-arsys.zip`; comprobación: `.sha256`; archivos: `dist/manifest.json`; instrucciones: `dist/LEEME-ARSYS.md`. Se regenera con `python3 tools/build-release.py` cuando cambie el código que se publicará.

## 6. Historial

La planificación anterior se conserva en [el archivo histórico](docs/pendientes-historico-previo-a-consolidacion-2026-11-21.md). Contiene decisiones y casillas de etapas ya superadas; **no es el checklist vigente**. Esta consolidación elimina contradicciones sobre claves, tabla provisional, Turnstile, worker y pruebas reales, sin perder ese historial.
