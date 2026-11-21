# Auditoría de Soto Digital — 21/11/2026

## 1. Resumen (cinco líneas)

1. Reforzada la protección Apache, caché, proxies, monitorización, copias y retención de leads.
2. Aplicada tabla acordada, móvil opcional, formulario en hero/servicios, variantes y medición con consentimiento.
3. Mejorados textos, cookies, accesibilidad, imágenes, rutas, esquema, sitemap y contenido Ads en borrador.
4. Pruebas locales correctas con proveedores simulados; sin publicar ni acceder a cuentas reales.
5. Pendientes: activación en Arsys/cuentas, contenido autorizado, alternativa sin JS y confirmar rendimiento en hosting.

Rama `auditoria-2026-11`. No había repositorio Git: se registró la instantánea inicial en `85ee21d`. No hay remoto publicado. Se han mantenido captación solo por formulario, IA sin datos personales y sin decisión automática, no newsletters y noindex en campañas/API. Fuera de alcance: propuestas, firma, pagos, freelancers.

## 2. Tareas y commits

«Hecha» indica implementación local; no implica activación en producción. «Parcial» identifica comprobaciones o aceptación pendientes. Los commits posteriores de contraste/rutas completan las tareas originales; consultar `git log`.

| Nº | Estado | Archivos principales | Commit inicial |
| --- | --- | --- | --- |
| 0.1 | hecha | .htaccess, robots.txt, .deployignore, .gitignore | `c2430a1` |
| 0.2 | hecha | .htaccess, campaign-config.php, docs/htaccess-local.example | `bbd0143` |
| 0.3 | hecha | .htaccess | `7b5f7e8` |
| 0.4 | hecha | .htaccess, campanas/.htaccess, api/.htaccess | `d1e1b68` |
| 0.5 | hecha | campaign-network.php, campaign-audit.php | `174f2d6` |
| 0.6 | parcial | campaign-operations.php, api/health.php, bin/campaign-leads.php | `ac1ff83` |
| 0.7 | hecha | campaign-operations.php, CLI | `5388e3d` |
| 1.1 | hecha | campaign-questionnaire.php, campaign-leads.php, tests | `ac7361f` |
| 1.2 | hecha | campaign-form.php, campaign-leads.php | `4f63b24` |
| 1.3 | parcial | js/campaign.js, tests | `5f7da43` |
| 1.4 | hecha | api/lead.php | `6e4b9e8` |
| 1.5 | hecha | js/tracking.js, js/campaign.js | `8876b37` |
| 2.1 | hecha | campaign-page.php, css/campaign.css | `b260107` |
| 2.2 | hecha | header.php, footer.php | `19a3cfd` |
| 2.3 | hecha | campaign-services.php, campaign-context.php | `c315f5b` |
| 2.4 | parcial | campaign-content.php, team-section.php | `f5aa853` |
| 2.5 | hecha | campaign-page.php, campaign-services.php | `f3d33a3` |
| 2.6 | hecha | campanas/gracias.php | `83b8c3a` |
| 2.7 | hecha | index.php, team-section.php | `d2f7ba8` |
| 3.1 | parcial | api/token.php, campaign-context.php, campaign-bootstrap.js | `08b32dc` |
| 3.2 | hecha | campaign-bootstrap.js, campaign-form.php | `d069158` |
| 3.3 | hecha | tools/build-assets.py, img/, header.php, site.webmanifest | `ae73268` |
| 3.4 | hecha | JS/CSS, tracking-config.php, páginas de servicio | `eb4ea1c` |
| 3.5 | parcial | local-business.php | `457e7fb` |
| 3.6 | hecha | tools/build-sitemap.py, sitemap.xml, tarjeta/, 404.php | `159eba5` |
| 3.7 | hecha | service-page.php, páginas de servicio | `bbe8812` |
| 3.8 | parcial | google-ads.php, meta-ads.php, blog/ | `3b62a8d` |
| 3.9 | parcial | cookies.js, footer.php, CSS, rutas, tests y capturas | `43c960e` |
| 4.1 | hecha | footer.php, cookies.js | `1d838de` |
| 4.2 | hecha | campaign-operations.php, CLI | `48d9992` |
| 4.3 | hecha | privacidad.php, campaign-operations.php | `42a10c3` |
| 4.4 | parcial | aviso-legal.php, soto-config.example.php | `df9f320` |
| 5 | hecha | campaign-next-stage.php, CLI, docs/siguiente-etapa-crm.md | `e4ab484` |

Precisiones de los parciales:

- **0.6:** health/heartbeat/alerta y pruebas implementados. Un cron detenido o Brevo caído necesitan monitor externo por una vía independiente; el equipo debe darlo de alta. No se considera entrega real de email probada.
- **1.3:** avance y acceso por teclado implementados; sin JS se ven todas las preguntas, pero Turnstile/tokens impiden envío completo. Se explica con noscript; falta decidir alternativa antispam sin JS.
- **2.4:** rótulo «Qué entregamos», equipo con datos aportados y módulos condicionales. Sin fotos, vídeos, testimonios ni permisos inventados. Clientes destacados de campañas ocultos hasta autorización; proyectos preexistentes de la web conservados, sujetos a revisión de permisos del equipo.
- **3.1:** HTML sin sesión/tokens y caché correcta; circuito de envío completo probado. No se cumple aún el criterio estricto de LCP sin empeorar en home, ver medición.
- **3.5:** esquema enlazado sin horario/dirección invisibles; falta validación externa de resultados enriquecidos sobre URL publicada. La revisión local no sustituye esa prueba.
- **3.8:** páginas Ads y blog preparados, pero deliberadamente noindex/borrador hasta aprobación. No se anuncian como páginas ya indexables.
- **3.9:** cero incidencias axe en las vistas finales, capturas y correcciones reales. Lighthouse no demuestra rendimiento igual o mejor en home. Pendiente prueba con lectores de pantalla y hosting real.
- **4.4:** campos registrales ocultos hasta aportar datos; contratos/transferencias y configuración definitiva pendientes del cliente.

Correcciones adicionales: `a04935a` redirección JSON absoluta tras retirar base; `286028b`, `e8fa5b5`, `71e74c7` contraste/metadatos; `d80e4e8` espacio de cabecera; `fff9637` CLI operativa sin subir herramientas de desarrollo y aislamiento del trato CRM opcional. La lectura de Authorization se ha comprobado también en Apache real (`f663b0e`). La tabla se refiere a los commits de implementación; documentación y evidencias se registran en el commit final de la rama.

## 3. Pruebas y evidencias

### Antes y después

La línea base se ejecutó antes de editar: pruebas PHP y frontend correctas, 50 PHP sin errores de sintaxis. Evidencias originales en `docs/auditoria-2026-11/php-tests.txt`, `frontend-tests.txt`, `php-lint.txt`. Instantánea inicial original servida separadamente en Apache local: solo se neutralizó su ruta local de configuración para inyectar datos ficticios, sin cambiar diseño ni lógica.

Después:

```sh
php tools/tests/campaign-leads.php
php tools/tests/campaign-audit.php
node tools/tests/campaign-frontend.js
SOTO_PHP=/ruta/php SOTO_AUDIT_NODE=/ruta/node python3 tools/tests/audit-static.py
python3 tools/build-assets.py
SOTO_PHP=/ruta/php python3 tools/build-sitemap.py
```

Resultado: PASS de validación, puntuación 0/3/4/6/7/10, móvil opcional/obligatorio, históricos, antispam, 5/hora, minimización IA, cola/fallos, emails idempotentes, proxies IPv4/IPv6, health, alertas simuladas, copias/rotación, purga y retención, reporte agregado y CRM desactivado. Camino CRM activado probado solo con transporte simulado e IDs de auditoría; comprueba asociación y un único trato. **64 PHP sin errores**, manifiesto coherente con máximo dos versiones, metadatos dentro de límites y sitemap con 14 URLs públicas únicas.

Prueba Apache real en instancia aislada con mod_php/rewrite/headers (no se modificó XAMPP habitual): rutas internas 404, VCF/assets 200, lead GET 405 esperado, tokens solo POST, API/campañas noindex, cabeceras de seguridad, una Cache-Control por respuesta y HTML inicial sin Set-Cookie. Endpoint health sin token 401 y con bearer correcto 200/ok. Configuración faltante devuelve fail/404 sin exponer datos. Evidencia `final-http.json`.

Playwright, 390 y 1440 px: home, campaña Google Ads, servicio, aviso legal, privacidad, cookies y gracias tras envío. En la pasada final: 14 vistas, cero errores JS/HTTP propios, cero desbordamientos, cero incidencias axe. Un evento generate_lead al llegar a gracias, cero al recargar. Tokens, Turnstile, guardado, cola, Brevo, IA y ambos emails probados con datos sintéticos y transporte simulado; **no se contactó con proveedores reales ni se enviaron correos reales**. `retry` seguido de `health`: todos los checks ok con la configuración de pruebas. Capturas y JSON antes/después guardados en `docs/auditoria-2026-11/`.

La revisión visual cubrió el formulario/hero móvil, tarjetas, gracias, servicio escritorio, privacidad y banner; se corrigieron contraste, separación de cabecera y rutas. Axe automatizado no equivale a conformidad WCAG completa.

### Lighthouse móvil

Tres pasadas por página y estado, Chrome local aislado, simulación móvil por defecto de Lighthouse, mismas URLs relativas en puertos distintos para original/final. Peticiones HTTPS externas bloqueadas para no llamar a proveedores reales. Resultados de laboratorio local, **no PageSpeed de producción ni Core Web Vitals de visitantes**. La auditoría histórica `docs/auditoria-rendimiento-movil.md` (16/10) no obtuvo puntuaciones por cuota/navegador; no hay baseline numérico histórico contra el que afirmar mejora.

Medianas de la línea base original y repetición final:

| Página | Rendimiento antes → después | Accesibilidad antes → después | LCP ms antes → después | CLS antes → después |
| --- | --- | --- | --- | --- |
| home | 100 → 98 | 96 → 100 | 1887 → 2320 | 0 → 0 |
| google-ads | 100 → 100 | 95 → 100 | 912 → 917 | 0 → 0 |
| servicio | 100 → 100 | 96 → 100 | 868 → 918 | 0 → 0 |

La home pierde dos puntos de mediana y su LCP aumenta aproximadamente 0,43 s; **no se da por cumplido ese criterio de aceptación**. En ambas versiones el elemento LCP identificado es el texto del aviso de cookies, no la imagen LED. El tiempo de renderizado real de ese elemento estuvo alrededor de 0,35 s en muestras comparables, mientras el LCP simulado varía. Se conservan todos los informes, incluido un pase inicial y repeticiones con outliers (61 en home base; 64 en un pase final Google y servicio por LCP ~4,9 s). No se excluyen para fabricar una mejora; las medianas reducen su impacto. Accesibilidad final 100 en todas las muestras de la repetición. Repetir en hosting con recursos reales antes de afirmar una mejora de rendimiento.

Seguridad: búsqueda de patrones de claves Brevo/Anthropic y claves privadas en todos los blobs del historial sin coincidencias. Esto es una comprobación de patrones, no certificación de ausencia de cualquier secreto. No se leyó la configuración privada real. No se publican docs, pruebas ni datos sintéticos; revisar exclusiones del cliente FTP.

## 4. Riesgos y decisiones del equipo, por impacto

1. **No activar publicidad sin circuito real:** Arsys, carpeta privada, HTTPS, claves/lista/atributos, remitente/DNS, worker/backup/purge y monitor externo con alertas independientes. Un health local verde no valida la cuenta ni entregabilidad real.
2. **Oferta/LED:** aprobar alcance y plazo de mini-auditoría, precios si se publican y estado operativo real de pantalla; no prometer audiencias sin datos.
3. **Privacidad:** Registro Mercantil, contratos con Arsys/Brevo/Cloudflare/Anthropic y transferencias, cookies efectivas, política de backups y última comunicación/relación comercial. Newsletters y purga remota Brevo desactivadas. Retención local necesita cron y mantenimiento de estados comerciales; copias pueden retener datos hasta rotación.
4. **Medición:** IDs/label, consentimiento real y Tag Assistant; una única acción principal para evitar doble conteo. CSP solo Report-Only; HSTS comentado hasta HTTPS verificado. Dealfront opcional sin activar y sin garantía de identificar personas.
5. **Contenido:** permisos/logos/resultados, fotos, vídeos subtitulados, aprobación de páginas Ads/blog y verificación Google Business Profile Maliaño. No se han inventado datos ni enviado solicitudes a clientes.
6. **Aceptación técnica parcial:** alternativa sin JS, rendimiento home, validación externa del esquema y pruebas asistivas. Las rutas absolutas requieren dominio/vhost en raíz.
7. **Siguiente fase:** pipeline/tratos con IDs reales e interruptor desactivado. Conversiones offline solo diseñadas, sin exportador ni importación. [Diseño CRM/offline](siguiente-etapa-crm.md).

El listado operativo vigente está al principio de `pendientes.md`; los apartados inferiores se conservan como historia y no prevalecen.

## 5. Despliegue seguro y rollback

1. Copia del sitio/reglas/configuración y backup consistente de leads; verificar restauración y conservar el paquete anterior completo.
2. Revisar Arsys/extensiones/permisos y completar configuración privada fuera de public_html, contratos/cuentas, DNS e IDs. No compartir claves por chat ni ponerlas en navegador.
3. Preparar paquete completo excluyendo docs/tools/pendientes/Git/datos; incluir `bin/campaign-leads.php` (CLI bloqueada por HTTP) y recursos/manifiesto coherentes. Preferir cambio atómico o mantenimiento durante subida.
4. Configurar cron y monitor externo; validar health y una prueba propia con servicios reales, emails, cookies, conversión y mobile/desktop antes de anuncios.
5. Si falla: detener anuncios, pausar escrituras/cron y recuperar el paquete anterior. No restaurar BD vieja sobre leads nuevos: conservar la actual y reconciliar primero. Restaurar SQLite solo si hace falta, con conexiones detenidas, integridad/permisos verificados y reaplicando supresiones. Repetir captación y health antes de reanudar.

Detalle y comandos en [publicación y recuperación](publicar-rendimiento.md) e [integración de leads](integracion-leads.md).
