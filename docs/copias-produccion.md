# Copia y restauración aislada de leads

Preparado el 25/11/2026. No afirmar que la copia de Arsys se ha ejecutado hasta recibir su resultado autenticado.

## Subida

Subir `api/backup.php`, `includes/campaign-backup.php` e `includes/campaign-operations.php` a sus respectivas carpetas dentro de `html/`. Mantener copia de los archivos que se sustituyen. No tocar la base activa ni subir configuración privada desde el Mac.

## Ejecución

En cron-job.org crear «Soto Digital — Copia de leads»: URL `https://soto-digital.com/api/backup.php`, POST, cuerpo vacío, cabecera `X-Soto-Worker-Token` con el mismo valor privado de la tarea de procesamiento. Guardar respuestas y activar avisos tras fallo/recuperación. Frecuencia diaria, por ejemplo 03:32 Europe/Madrid para evitar el minuto del worker. Timeout inicial 30 segundos; no garantiza tiempo suficiente para bases grandes.

La prueba devuelve HTTP 200 con `ok: true`, `integrity: ok`, `restore_test: ok`, `leads`, `backup_file` y `sha256`. La copia se guarda dentro de `CAMPAIGN_DATA_DIR/backups` (habitualmente `data/soto-private/leads/backups`). Un 429 con `retry_later` indica bloqueo ocupado o ejecución repetida antes de cinco minutos; repetir después. Un 503 indica que la copia o verificación ha fallado; investigar antes de marcarla completada.

La base se copia consistentemente con SQLite VACUUM INTO cuando está disponible. Arsys devolvió `sqlite_vacuum_into_unsupported` en fase snapshot; se añadió una alternativa compatible que usa una transacción de lectura para copiar esquema, tablas, índices, vistas y triggers a una base nueva. La alternativa está probada localmente, incluida lectura de la restauración y modificación de esta sin tocar el original; falta repetirla en Arsys tras subir `includes/campaign-operations.php`. Se copia de nuevo a un archivo de prueba separado, se compara SHA256, se abren ambos archivos en modo de solo consulta y se verifica integridad y número de registros de las tres tablas. El archivo de restauración de prueba se elimina al terminar. No se ejecutan envíos, sincronizaciones ni acciones de IA contra las copias. No se modifica ningún lead activo; se registra la fecha de la copia correcta como dato operativo.

Solo después de esa prueba se rotan copias antiguas con el patrón generado por el sistema. Se conservan 14 por defecto; `CAMPAIGN_BACKUP_KEEP` ajusta el límite entre 2 y 90. Directorio 0700 y archivos 0600, fuera del directorio público. No se admiten enlaces simbólicos para el directorio de copias.

## Recuperación y custodia

Descargar una copia validada por SFTP a almacenamiento privado externo y conservar su checksum. No subirla a GitHub ni al directorio público. Esta operación cubre SQLite de leads; no es una copia de web, claves o datos de Brevo.

Una restauración real exige parar las escrituras y el worker, preservar los leads posteriores a la copia y reaplicar supresiones. No sustituir la base activa simplemente para probarla. El ensayo aislado acredita lectura e integridad de la copia, no un simulacro de recuperación de toda la infraestructura.


## Resultado real en Arsys

El 25/11/2026 el endpoint autenticado devolvió HTTP 200: copia `leads-20261125-183854-21650808.sqlite`, integridad y restauración aislada correctas, 5 leads y límite de retención de 14 copias. SHA256 e informe en `prueba-backup-produccion.json`. No se ha reemplazado la base activa. Aún falta programar la ejecución diaria y custodiar una copia externa descargada por SFTP.
