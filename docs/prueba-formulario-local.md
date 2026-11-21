# Prueba del formulario local — 21/11/2026

Estado: prueba local completa superada. Turnstile real, guardado y página de gracias verificados.

- Entorno: XAMPP, http://localhost/sotodigital/.
- El usuario creó el widget e incorporó las claves reales; no se publican ni se versionan.
- Datos privados: carpeta leads fuera de htdocs, con permisos restringidos. ACL locales para Apache daemon y el usuario local autorizado; no se han concedido permisos globales de escritura.
- Archivo externo Apache: etc/extra/httpd-sotodigital-local.conf, incluido desde httpd.conf. SOTO_CONFIG_FILE se aplica únicamente al directorio de esta web. Estas rutas y ACL son locales y no deben copiarse a Arsys.
- Sintaxis Apache comprobada: Syntax OK. El usuario efectuó la recarga administrativa.
- HTML real: widget Turnstile presente, botón de envío habilitado y sin errores PHP.
- POST api/token.php real: emisión correcta de tokens y sesión.
- Detectado y corregido: CAMPAIGN_RATE_SECRET vacío en archivo privado anulaba el fallback. Se usa el secreto Turnstile cuando el opcional está vacío; se mantiene el HMAC y el límite de cinco intentos/hora.
- POST api/lead.php con token inválido: 422 y rechazo antispam. Consulta de solo lectura confirma cero leads guardados; no se ha eludido ni simulado Turnstile.
- La primera prueba falló con 503 antes de corregir el secreto y pudo activar el aviso operativo configurado. No representa un lead aceptado.
- Navegador compartido no disponible para el agente. Se pide al usuario completar una solicitud identificada como PRUEBA FORMULARIO con el buzón propio ads@soto-digital.com y confirmar si llega a gracias.

## Envío válido comprobado

El usuario completa el formulario en su navegador el 21/11/2026 a las 17:44:40 (Europe/Madrid):

- POST /sotodigital/api/lead.php → 200.
- GET /sotodigital/campanas/gracias.php → 200; el usuario confirma que ve gracias.
- Consulta SQLite de solo lectura: un lead guardado, servicio estrategia-digital, landing /, puntuación PHP 5/10, potencial templado, versión v1-acordada-2026-11-21 y privacidad legal-v3.
- Validación Turnstile real superada por el endpoint: no se simuló el token ni se deshabilitó el antispam.
- El lead queda en cola: Brevo y ambos emails aún pendientes del worker, según el alcance de esta fase.
- No se ha probado recargar gracias en este envío real; la deduplicación cuenta con pruebas automatizadas anteriores.

## Corrección de mensajes durante la prueba

El primer intento del usuario no generaba POST al endpoint; se refuerzan los mensajes de validación del navegador y se sitúa el aviso junto al botón. Se explica el formato de URL y la casilla de privacidad. La preparación del formulario consulta getResponse del widget, con fallback al campo oculto, y rechaza respuestas caducadas. No se atribuye el bloqueo a un campo concreto, porque el navegador compartido no estuvo disponible. Tras recargar con estos cambios, el usuario sí envió y llegó a gracias. Pruebas automatizadas de campos inválidos, error API y navegación correctas.

Esta fase no ejecuta el worker de la cola real ni configura el cron. La aceptación del formulario acredita el guardado local; la prueba directa de Brevo ya está documentada por separado. La operación automática en Arsys sigue pendiente.

## Sincronización real posterior solicitada por el usuario

Al indicar el usuario que aún no veía el lead, se comprobó una solicitud pendiente y se ejecutó manualmente el worker con la configuración privada real. Consulta posterior: brevo_synced=true, contacto ID 101 con PUNTUACION=5 y POTENCIAL=templado, pertenencia a la lista correcta. Al compartir email con la primera prueba se actualiza ese contacto en vez de duplicarlo, conservando la solicitud local. Brevo acepta aviso y confirmación (ambos estados sent); en esta comprobación no se consultan eventos de entrega. IA sigue pending: no se da por probada ni configurada. La ejecución manual no configura el cron ni automatiza futuros envíos.

## Automatización local posterior

A petición del usuario se instala com.sotodigital.leads-worker como LaunchAgent de su usuario, cada 300 segundos y al cargar/iniciar sesión. Primera ejecución automática verificada: exit code 0, heartbeat reciente y cero contactos/emails pendientes. No se crea cron en Arsys ni se generan leads adicionales. El Mac debe estar despierto y la sesión abierta; ver `worker-local.md`.
