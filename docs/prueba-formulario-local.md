# Prueba del formulario local — 21/11/2026

Estado: configuración lista; prueba negativa superada; envío válido pendiente del usuario.

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

## Pendiente de verificar tras el envío válido

1. Registro local realmente guardado y puntuación PHP correcta.
2. Navegación a gracias; no duplicar al recargar.
3. Datos rechazados no generan leads ni conversión.

Esta fase no ejecuta el worker de la cola real ni configura el cron. La aceptación del formulario acredita el guardado local; la prueba directa de Brevo ya está documentada por separado. La operación automática en Arsys sigue pendiente.
