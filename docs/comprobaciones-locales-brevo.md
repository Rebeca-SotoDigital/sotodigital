# Siete comprobaciones locales de Brevo — 21/11/2026

## Resultado

| Paso | Estado | Evidencia y alcance |
| --- | --- | --- |
| 1. Automatizaciones | Confirmado por el equipo | El usuario revisa Brevo e indica que no hay workflows activos. La API disponible no confirmó esa configuración y no hubo navegador conectado; no se afirma una revisión independiente del panel. El código sigue sin newsletters, sin crear automatizaciones ni modificar listas de bloqueo. |
| 2. Atributos | Hecho y verificado | Se crean los 14 atributos de texto que faltaban y se sincronizan los dos contactos internos de prueba. Lectura posterior confirma SERVICIO, ID_SOLICITUD y VERSION_PRIVACIDAD en ambos. No se han inventado identificadores de clic o campañas. |
| 3. Errores y duplicados | Pruebas superadas | HTTP aislado: 403 sin sesión, 200 con envío válido, repetición 200 con un único lead y 403 al reproducir en otra sesión. Prueba de doble clic: una sola petición desde JS. Mocks de caída de Brevo/IA y estados ambiguos conservan lead y evitan reenvíos ciegos. Ningún proveedor real participa en estas simulaciones. |
| 4. Alertas, backup/restauración | Comprobado | Copia SQLite real consistente, modo 0600; restauración en temporal aislado, integrity_check=ok y ambos registros iguales. Alerta real de prueba entregada a ads; repetición inmediata suprimida. Health local: 401 sin token, 200/ok con token privado. Worker parado, cola antigua y BD de solo lectura simulados correctamente. |
| 5. Conservación | Simulación comprobada | Dry-run en BD real: eligible=0, deleted=0. En BD aislada se verifica eliminación de antiguos, protección comercial, última comunicación reciente y fallo de borrado remoto. No se borran leads reales ni contactos Brevo. |
| 6. Emails | Actualizados y entrega verificada | HTML responsive, texto más claro, datos escapados, información interna solo al equipo y sin teléfono en confirmación. Un correo de prueba a rebeca@soto-digital.com entregado a las 18:29:30; alerta ads entregada a las 18:29:31 (Europe/Madrid). No se crean contactos para estas pruebas de recepción. |
| 7. Paquete Arsys | Preparado, sin publicar | dist/soto-digital-arsys.zip, SHA256, manifiesto, ejemplo privado vacío y LEEME. Solo archivos web versionados, sin datos, claves, docs ni herramientas. La CLI bin necesaria sí está incluida y bloqueada por HTTP. Carpeta dist bloqueada por HTTP (404). |

## Cambios de código

- `includes/campaign-emails.php`: plantillas separadas para aviso y confirmación, HTML sin recursos externos y versión textual interna. `campaign_send` usa htmlContent si hay HTML o textContent para mensajes de texto, sin mezclar cuerpos.
- `campaign-operations.php`: IA opcional no provoca un falso fallo de salud de captación/Brevo. Si se exige mediante CAMPAIGN_REQUIRE_AI=true, una clave IA ausente sí falla. El CLI avisa expresamente cuando la IA no está configurada; no se da por activada ni validada.
- Token de salud generado y guardado exclusivamente en configuración privada; no se muestra ni se incluye en Git/ZIP.
- `.htaccess`, `.gitignore`, `.deployignore`: dist bloqueado y excluido del despliegue/Git. Los archivos de trabajo sin seguimiento Git (incluido el trabajo independiente de WhatsApp) no entran en el paquete.
- `tools/build-release.py`: empaquetado repetible con exclusiones, revisión de patrones de claves privadas/proveedor y rutas locales, ZIP verificado y permisos de archivos web 0644. Estas comprobaciones no certifican ausencia de todos los secretos posibles.
- Verificador local adaptado para no ejecutar como páginas públicas archivos de configuración no versionados.

## Pruebas y artefactos

- `docs/auditoria-2026-11/final-tests.txt`: suites PHP/JS, sintaxis, metadatos y recursos.
- `docs/comprobaciones-locales-http.json`: pruebas HTTP con proveedores simulados.
- `docs/comprobaciones-locales-brevo.json`: resultados reales acotados, sin claves ni payloads de clientes.
- `docs/email-preview-admin_sent.html` y `docs/email-preview-confirmation_sent.html`: muestras con datos ficticios. Revisión automatizada de escape HTML, destinatarios y separación de datos internos; no se afirma revisión visual en clientes de correo no disponibles.
- `docs/integracion-leads.md`, `docs/publicar-rendimiento.md` y `dist/LEEME-ARSYS.md`: operación, publicación y recuperación.

## Pendiente para producción

1. Arsys: acceso, PHP/extensiones, HTTPS, carpetas/permisos privados, SOTO_CONFIG_FILE y cron cada cinco minutos. El LaunchAgent del Mac no sirve como tarea de Arsys.
2. Autorizar IP saliente de Arsys en Brevo y crear/configurar widget Turnstile para el dominio publicado; el widget local no basta.
3. Programar backup, conservación y monitor externo con una vía de aviso independiente. La prueba de copia/restauración y de alerta no instala esos servicios en Arsys. Una caída de Brevo no puede notificarse fiablemente por Brevo.
4. Revisar la recepción en Gmail/Outlook si se facilitan buzones propios de esos proveedores. La dirección facilitada para esta tarea fue del dominio Soto Digital; se verificó entrega a su servidor, no lectura ni ubicación en bandeja principal.
5. Repetir formulario → almacenamiento → worker → Brevo → ambos emails y medición en la web publicada antes de anuncios. Mantener los plazos/contratos/inventario de proveedores definitivos y aprobación de oferta/contenidos.

La integración no comprueba automáticamente la entrega de todos los mensajes: sent significa aceptado por la API. En estas pruebas sí se consultaron los eventos delivered; los rebotes de destinatarios reales requieren seguimiento en Brevo o una integración de eventos posterior.
