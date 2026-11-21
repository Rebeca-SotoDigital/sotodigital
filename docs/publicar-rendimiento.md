# Publicar y recuperar la web

Auditoría local del 21/11/2026. No se ha publicado ni cambiado producción. Informe: `docs/auditoria-2026-11.md`.

## Antes de subir

1. Identificar versión PHP y módulos `pdo_sqlite`, `curl`, `mbstring`, soporte JSON de SQLite y Apache con rewrite/headers. El dominio final se publica en raíz. La vista local también admite subcarpetas: PHP detecta el prefijo y adapta recursos, enlaces y formulario. Comprobar HTTPS estable antes de activar HSTS (comentado).
2. Hacer copia completa del sitio, reglas y configuración privada; `php bin/campaign-leads.php backup` para copia consistente de leads. Verificar integridad y restauración, permisos 0600 y copia fuera del servidor. No subir esa copia al directorio público.
3. Copiar `docs/soto-config.example.php` **fuera** de la carpeta pública, completar valores reales y apuntar `SOTO_CONFIG_FILE` a esa ruta. Definir la misma variable para PHP web y CLI/cron. No publicar ni versionar claves.
4. Completar cuentas/atributos/DNS descritos en `docs/integracion-leads.md`; elegir remitente, plazo de respuesta y proxies de confianza solo si existen. No activar tratos ni purga remota Brevo todavía. Monitor protegido con token largo y alertas por una vía independiente de Brevo/worker.
5. Ejecutar tests y `python3 tools/build-assets.py`, `python3 tools/build-sitemap.py`. Revisar borradores: Google/Meta Ads/blog permanecen noindex hasta aprobación. No cambiar noindex de campañas/API.

## Paquete y publicación

`.deployignore` es una lista de exclusión, **no una protección automática en FTP**. Configurar y comprobar exclusiones en el programa que se use. Excluir `.git`, docs, tools, pendientes, archivos locales, datos, logs y secretos. **Incluir `bin/campaign-leads.php`**, entrada operativa CLI; el wrapper de `tools/` es solo para desarrollo. Apache bloquea también `bin/` por HTTP y el propio script exige CLI. No publicar los fixtures de tests.

Crear una versión completa nueva con PHP, `includes/asset-manifest.php`, CSS/JS con hash, fuentes, imágenes/iconos, manifest, reglas raíz/campañas/API, sitemap y robots. Preferir cambio atómico del directorio/release. Si solo hay FTP, ventana de mantenimiento para no mezclar plantillas/manifiesto/recursos: recursos primero, plantillas y manifiesto como una unidad después. No ejecutar el limpiador de hashes sobre una versión en uso durante una subida parcial. El build conserva dos versiones por grupo; conservar además el paquete completo de la versión anterior para rollback.

Configurar cron con PHP CLI y rutas absolutas del servidor: retry cada 5 minutos, backup diario, purge diario tras revisar `--dry-run`, report semanal. El código operativo está en `bin`, sin depender de herramientas excluidas. Si el hosting no admite cron, no activar anuncios hasta acordar alternativa.

## Comprobación posterior antes de anunciar

- Home sin Set-Cookie inicial, HTML `no-cache`, API/gracias `private, no-store`, campañas/API noindex. Una única Cache-Control por respuesta. Recursos con hash immutable un año, imágenes sin hash 30 días.
- 404 reales en docs/includes/tools/bin/pendientes/archivos ocultos. Assets y tarjetas VCF siguen accesibles. Comprobar 404 propia y enlaces.
- Un lead propio: guardado, gracias, worker, contacto Brevo, resumen sin datos personales, aviso y confirmación, conversión una sola vez con permiso. Probar rechazo/retirada de cookies, token inválido y caída controlada del proveedor.
- `health` correcto y aviso real del monitor independiente; confirmar cron y copias. CSP está en Report-Only, no bloquea ataques: revisar violaciones con proveedores reales antes de imponerla. Comprobar formulario Turnstile y consola sin errores reales.
- Revisar capturas móvil/escritorio, teclado, lectores de pantalla, Tag Assistant, esquema y tres pasadas de Lighthouse en hosting. Resultados locales no son Core Web Vitals de usuarios.

## Vuelta atrás

Detener publicidad si la captación falla. Pausar cron y escrituras durante la recuperación. Conservar la BD actual y restaurar el paquete completo anterior con su manifiesto/recursos/reglas. Las migraciones son aditivas, pero comprobar compatibilidad antes de cambiar código. **No sobrescribir la BD con una copia anterior si hay leads nuevos**: preservar y recuperar esos registros primero. Restaurar BD únicamente si es necesario, con conexiones cerradas, integridad verificada y propietario/permisos correctos; gestionar WAL/SHM con todas las conexiones detenidas. Reaplicar supresiones realizadas desde la copia. Reanudar cron, health y una prueba de captación; luego publicidad.
