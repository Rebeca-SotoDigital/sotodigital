# Soto Digital

Web de Soto Digital en PHP, CSS y JavaScript, con landings de servicios, formulario, sincronización de contactos en Brevo y resumen de solicitudes mediante Claude.

## Desarrollo

El proyecto se revisa en local con XAMPP. La configuración real debe estar fuera del directorio público y del repositorio; PHP la recibe mediante `SOTO_CONFIG_FILE`. La plantilla sin claves está en `docs/soto-config.example.php`. El almacenamiento de leads, las copias y los logs también deben ser privados.

No subir claves, archivos `.env`, configuraciones reales, bases de leads ni certificados. `.gitignore` excluye los archivos privados conocidos. Revisar siempre los cambios antes de un commit.

## Verificación

Con PHP y Node disponibles, ejecutar `python3 tools/tests/audit-static.py`, indicando `SOTO_PHP` y `SOTO_AUDIT_NODE` si no están en el PATH. Las pruebas usan transportes simulados y no envían mensajes reales. El worker operativo se ejecuta con `php bin/campaign-leads.php retry`; requiere configuración privada y puede sincronizar contactos y enviar correos pendientes.

## Git

Remoto: `https://github.com/Rebeca-SotoDigital/sotodigital.git`. Rama principal: `main`.

Para guardar cambios: revisar `git status` y `git diff`, añadir solo los archivos correspondientes con `git add`, crear un commit y ejecutar `git push`. Las credenciales se gestionan mediante GitHub CLI, nunca mediante tokens en la URL del remoto.

El trabajo independiente de WhatsApp permanece fuera de los commits de esta integración hasta su revisión específica. La configuración real de Analytics tampoco se incluye; deberá configurarse en cada entorno.

## Estado y publicación

Consultar [pendientes.md](pendientes.md). Subir código a GitHub no publica la web en Arsys. No hay despliegue automático configurado por esta integración; la publicación sigue pendiente de revisión y autorización del equipo.
