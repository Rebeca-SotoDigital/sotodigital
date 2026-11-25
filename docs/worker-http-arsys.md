# Procesamiento de leads sin cron del hosting

Implementación activada por el equipo el 25/11/2026 en Arsys con cron-job.org. Capturas confirman ejecución autenticada HTTP 200 y ejecución automática cada cinco minutos. El equipo confirma contactos en Brevo con puntuación y resumen de IA, recepción de ambos correos y `pending: 0` tras autorizar la IP de Arsys. Canal de avisos probado: captura del 25/11/2026 a las 18:47 confirma email a info@soto-digital.com por un 401 programado de la tarea temporal «Prueba de alertas». Pendientes eliminar esa tarea temporal, confirmar los avisos en la principal y configurar monitor de salud independiente para fallos no reflejados por un HTTP 200.

## Envío de formulario

Una solicitud validada se guarda primero en SQLite privado. Se cierra la sesión PHP y se intenta sincronizar el contacto y enviar los correos durante un máximo aproximado de 15 segundos de presupuesto de red. El formulario puede tardar unos segundos más. Un fallo de proveedor no deshace el guardado ni impide confirmar recepción. Si el worker ya está ocupado, la solicitud queda en cola.

La IA se difiere al procesador de cola. Por eso el primer aviso puede no contener todavía el resumen; al completar la IA, Brevo se actualiza sin reenviar los dos correos. La puntuación sigue calculándose en PHP.

## Endpoint de reintentos

- URL: `https://soto-digital.com/api/worker.php`.
- Método: **POST**, cuerpo vacío.
- Cabecera: `X-Soto-Worker-Token` con un secreto independiente de al menos 32 caracteres, guardado en `CAMPAIGN_WORKER_TOKEN` en el archivo privado de Arsys.
- Nunca poner el secreto en la URL, un formulario público, GitHub o capturas compartidas.
- Sin configurar: 404. Clave incorrecta/ausente: 401. Método incorrecto con clave válida: 405. Almacenamiento no disponible: 503.
- Procesa hasta tres solicitudes y aproximadamente 22 segundos de presupuesto de red por llamada. Bloqueo compartido con CLI y envío inmediato. Peticiones programadas separadas al menos 30 segundos.
- Devuelve solo contadores: procesados, pendientes y correos que requieren revisión. HTTP 200 confirma que el procesador se ejecutó, no que todos los proveedores entregaron todo; revisar `pending` y `email_review`.
- No permite exportar, borrar contactos, cambiar destinatarios o ejecutar comandos arbitrarios. Un correo con resultado ambiguo permanece marcado para revisión, sin reenvío automático ciego.

## Activación por SFTP

1. Hacer copia de los cinco archivos que se sustituyen o incorporan: `api/lead.php`, `api/worker.php`, `includes/campaign-leads.php`, `includes/campaign-worker.php`, `includes/campaign-config.php`. Los nuevos no tendrán copia anterior. No borrar la base de leads.
2. Subir los archivos del ZIP de parche conservando las carpetas, sobre `html/`. No subir el ZIP ni documentación al directorio público.
3. Añadir la entrada `CAMPAIGN_WORKER_TOKEN` al array de `/data/soto-private/soto-config.php`, con el secreto generado localmente; mantener comas y claves existentes. No compartir el archivo completo. `CAMPAIGN_PROCESS_ON_SUBMIT` es true por defecto y puede desactivarse explícitamente.
4. En cron-job.org (o un proveedor compatible con POST y cabeceras privadas), crear tarea cada cinco minutos con URL, método y cabecera anteriores, timeout de al menos 30 segundos y avisos de fallo. No activar una tarea por GET ni con un token en query string.
5. Ejecutar una vez la tarea para recuperar los leads ya almacenados. Si quedan pendientes, repetir pasados al menos 30 segundos; las llamadas periódicas drenarán la cola. No repetir formularios para recuperar solicitudes existentes.
6. Revisar estado de la tarea, contacto/lista y ambos correos en Brevo. Si el contacto no llega, comprobar la IP saliente de Arsys autorizada en Brevo, claves y atributos. Este parche no evita las restricciones de IP del proveedor.
7. Verificar un nuevo envío propio y la actualización posterior del resumen de IA. Mantener un monitor de salud independiente del programador y de Brevo.

## Validación realizada

Pruebas aisladas con proveedores simulados: procesamiento inmediato de CRM y emails, IA diferida con resincronización, preservación de los envíos, bloqueo concurrente, límites temporales, clave débil/incorrecta y autenticación solo por cabecera (token en URL rechazado). Regresiones de almacenamiento, Brevo, emails, seguridad, formulario y medición correctas. No se han enviado mensajes reales durante estas pruebas.

El programador externo requiere cuenta y configuración del equipo. No se crea una tarea simplemente al subir PHP por SFTP; sin programador externo no habrá reintentos periódicos ni IA automática para la cola existente.
