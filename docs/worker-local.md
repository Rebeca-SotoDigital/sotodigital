# Worker automático local — XAMPP / macOS

Activado el 21/11/2026 con autorización del usuario.

## Configuración instalada

- LaunchAgent de usuario: `/Users/macniacos/Library/LaunchAgents/com.sotodigital.leads-worker.plist`.
- Etiqueta: `com.sotodigital.leads-worker`.
- Frecuencia: 300 segundos; también se ejecuta al cargarlo/iniciar sesión.
- Comando: PHP CLI de XAMPP → `bin/campaign-leads.php retry` en el proyecto.
- SOTO_CONFIG_FILE apunta al archivo privado externo. El plist no contiene claves API.
- Salida y errores: `worker.log` y `worker-error.log` en la carpeta privada `sotodigital-private`, con permisos 0600.
- El worker ya existente impide ejecuciones concurrentes mediante bloqueo. Una ejecución posterior no reenvía emails registrados como enviados.

## Verificación realizada

Plist válido, LaunchAgent cargado, `runs = 1`, `last exit code = 0` y `run interval = 300 seconds`. Heartbeat de la BD actualizado; cero solicitudes pendientes de Brevo y cero emails pendientes. El trabajo aparece normalmente como not running entre ejecuciones: es periódico, no un proceso permanente. No se generaron nuevos leads de prueba.

## Operación

Estado:

```sh
launchctl print gui/501/com.sotodigital.leads-worker
```

Ejecutar sin esperar al siguiente intervalo:

```sh
launchctl kickstart gui/501/com.sotodigital.leads-worker
```

Desactivar si se decide detener el procesamiento automático:

```sh
launchctl bootout gui/501/com.sotodigital.leads-worker
```

Volver a activar:

```sh
launchctl bootstrap gui/501 /Users/macniacos/Library/LaunchAgents/com.sotodigital.leads-worker.plist
```

Los comandos anteriores corresponden al usuario local actual (UID 501). No ejecutarlos como root ni copiarlos literalmente a otro equipo.

## Límites y pendientes de producción

El Mac debe estar despierto y la sesión de este usuario abierta; no se garantiza procesamiento continuo al cerrar sesión o apagar el equipo. Debe haber conectividad con los proveedores. La configuración es local, no despliega nada ni crea cron en Arsys. Para producción, configurar el worker en ese servidor con sus rutas privadas y PHP CLI, junto con monitor, copias y conservación. El heartbeat no sustituye el monitor externo: un proceso detenido no puede avisar por sí mismo. La IA sigue dependiendo de su propia configuración; esta activación no acredita su funcionamiento.

Los logs no se publican; revisar su tamaño y establecer rotación antes de mantener esta instalación durante periodos largos.
