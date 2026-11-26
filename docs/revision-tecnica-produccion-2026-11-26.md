# Revisión técnica — 26/11/2026

El informe anterior de landings cubría únicamente estructura y recursos. Esta revisión amplía el alcance a 29 páginas y 38 recursos/enlaces internos en producción, sin enviar leads. Datos HTTP en `revision-tecnica-produccion-2026-11-26.json`.

## Correcto

- Páginas y recursos enlazados responden 200. Un H1 por página, imágenes con alt y anclas locales válidas.
- Salud y worker sin credenciales devuelven 401; `.git/config` devuelve 404. El endpoint de envío devuelve correctamente 405 ante GET.
- Suite local completa correcta, incluidas restauración aislada, retención, formularios, consentimiento y rutas.
- Recursos versionados y fuentes locales. Sitemap con 18 URL públicas y campañas fuera del índice.

## Incidencia pendiente en Arsys

`/includes/campaign-backup.php`, `/docs/soto-config.example.php`, `/tools/tests/campaign-backup.php` y `/dist/mejoras-landings.zip` responden 200. Las páginas y CSS no devuelven las cabeceras globales de caché/protección ni gzip solicitado. El `.htaccess` local ya define esas reglas: verificar que se sube como archivo oculto a `/html/.htaccess` y que Arsys lo aplica. No basta con robots.txt. No se han consultado claves privadas ni datos de leads.

Tras subirlo: comprobar 404 en includes/docs/tools/dist, cabeceras `X-Content-Type-Options`, `X-Frame-Options`, caché larga para recursos con hash, revalidación HTML y compresión de texto cuando Apache la permita. Si las reglas no se aplican, revisar con Arsys la configuración del directorio.

## Retención de copias

Subir `includes/campaign-backup.php`. El código limita la retención a tres incluso si la configuración privada conserva 14. El ejemplo de configuración pasa a 3. Se permite reducir a dos si se configura explícitamente. Solo se eliminan copias reconocidas después de crear y restaurar/verificar una nueva. La siguiente ejecución válida debe devolver `retention_limit: 3`. No modificar ni sustituir la base activa.

## Límite de la revisión

No se ha hecho una medición nueva de Lighthouse/Core Web Vitals ni una auditoría manual completa de accesibilidad. Las mediciones Lighthouse existentes corresponden al entorno local anterior. No afirmar puntuaciones de rendimiento actuales en producción.

## Incidencia resuelta

Verificación posterior a la subida del `.htaccess` el 26/11/2026: home y Google Ads 200; includes/docs/tools/dist/.git 404; salud/worker 401 sin token; compresión gzip, caché HTML no-cache, CSS con hash immutable un año y cabeceras nosniff/SAMEORIGIN correctas. Robots y sitemap 200. Evidencia en `verificacion-htaccess-produccion-2026-11-26.json`. Punto técnico cerrado dentro del alcance descrito.
