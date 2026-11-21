# Auditoría de recursos y preparación para rendimiento móvil

Fecha: 16 de octubre de 2026. Sitio: https://soto-digital.com/

## Alcance y limitaciones

Se consultaron la portada y las cuatro páginas de servicios publicadas, sus recursos principales y cabeceras HTTP. Se revisaron además las plantillas y estilos locales. Todas las URLs consultadas devolvieron HTTP 200.

Esta es una auditoría de recursos y código orientada a móvil, no una prueba de navegador con móvil simulado. La API pública de PageSpeed devolvió HTTP 429 por cuota agotada y no había navegador conectado disponible. No se han medido la puntuación Lighthouse, LCP, INP, CLS, consumo de CPU, cascada real de carga ni comportamiento visual. Tampoco se ha accedido a Search Console ni a datos reales de usuarios. Los tiempos HTTP obtenidos desde este equipo no representan una conexión móvil.

No se ha modificado el funcionamiento ni el diseño de la web durante la auditoría.

## Hallazgos y prioridades

| Prioridad | Hallazgo comprobado | Acción recomendada |
|---|---|---|
| Alta | El logo PNG descarga 126.918 bytes. El original local mide 1600 × 453 píxeles; el CSS lo muestra a 240 px de ancho como máximo en escritorio. | Generar un recurso más pequeño, mantener transparencia y usar variantes para pantallas de alta densidad. Comparar calidad antes de sustituirlo. |
| Alta | La recreación de la pantalla LED descarga 605.670 bytes para 693 × 595 píxeles. | Comparar WebP/AVIF con el PNG, manteniendo la composición y una alternativa compatible. La imagen ya usa carga diferida y dimensiones explícitas. Reducir su peso mejora la descarga al acercarse a esa sección; no se ha identificado como elemento LCP. |
| Alta | Font Awesome sirve 21.631 bytes comprimidos de CSS. Sus fuentes WOFF2 de marcas y símbolos sólidos pesan 108.020 y 150.124 bytes. Hay iconos de ambas familias en las plantillas. | Sustituir los pocos iconos utilizados por SVG equivalentes o servir un subconjunto. Las fuentes sumarían 258.144 bytes si el navegador descarga ambas; no se ha registrado una cascada real. |
| Alta | Las respuestas de HTML, CSS y JavaScript propios examinadas no incluyen Content-Encoding pese a solicitar compresión con curl --compressed. | Activar gzip/Brotli para texto en el hosting. No recomprimir imágenes o WOFF2. Validar la configuración en producción. |
| Media | CSS e imágenes propios examinados incluyen Last-Modified y ETag, pero no Cache-Control ni Expires. | Añadir una política explícita de caché para recursos estáticos y un sistema de versiones para invalidarla. No aplicar caché larga indiscriminada al HTML. La ausencia de directivas no implica que el navegador nunca pueda usar caché heurística. |
| Media | Google Fonts y Font Awesome se cargan mediante hojas de estilo en head; Google Fonts requiere además conexión a fonts.gstatic.com. No existen pistas preconnect. | Reducir dependencias de iconos, valorar fuentes locales WOFF2 y añadir preconnect a las conexiones externas que se mantengan. Conservar font-display: swap, ya existente. |
| Media | El logo no tiene width y height en HTML. | Declarar sus dimensiones y ajustar el CSS para reservar su proporción sin alterar el diseño. Es un riesgo de desplazamiento pendiente de medir, no un fallo CLS demostrado. |
| Baja | Hay dos hojas CSS propias globales y reglas de componentes que no se utilizan en todas las páginas. | Medir cobertura con navegador antes de eliminar reglas. No retirar CSS basándose solo en búsquedas de texto. |

## Tamaños observados

Bytes de descarga informados por curl; la compresión se solicitó en todas estas mediciones.

| Recurso | Bytes |
|---|---:|
| HTML portada | 20.888 |
| HTML diseño web | 12.892 |
| HTML redes sociales | 12.914 |
| HTML branding | 12.779 |
| HTML pantalla LED | 13.135 |
| style.css | 18.051 |
| redesign.css | 19.916 |
| services.css | 1.954 |
| main.js | 3.696 |
| cookies.js | 4.481 |
| Logo PNG | 126.918 |
| Imagen LED de la composición inicial (JPG) | 85.096 |
| Recreación LED (PNG, carga diferida) | 605.670 |
| Font Awesome CSS (gzip) | 21.631 |
| Font Awesome marcas WOFF2 | 108.020 |
| Font Awesome sólidos WOFF2 | 150.124 |

No se suman estos recursos como peso inicial: la imagen diferida puede descargarse después y las fuentes se solicitan según uso. Google Fonts adapta su respuesta al navegador; la respuesta obtenida con curl no permite calcular el peso tipográfico real en móvil.

## Compresión: comparación reproducible

Se comprimieron localmente con gzip los bytes publicados descargados. Es una estimación del ahorro de transferencia; el servidor puede producir tamaños diferentes.

| Recurso | Actual | Gzip local |
|---|---:|---:|
| HTML portada | 20.888 B | 6.159 B |
| style.css | 18.051 B | 3.856 B |
| redesign.css | 19.916 B | 5.209 B |
| Total | 58.855 B | 15.224 B |

Ahorro potencial en ese conjunto: 43.631 bytes, aproximadamente un 74 %. No equivale a un 74 % de reducción del tiempo de carga.

## Aspectos ya favorables

- El contenido principal se entrega como HTML generado por PHP, sin necesitar JavaScript para mostrarlo.
- No hay un framework JavaScript grande: los dos scripts propios suman 8.177 bytes.
- Los scripts propios están al final del documento.
- Las imágenes LED declaran dimensiones y la recreación usa loading="lazy".
- Google Fonts ya incluye display=swap.
- Las animaciones contemplan prefers-reduced-motion y las decorativas tienen iteraciones limitadas.
- Las páginas de servicios comparten plantilla y recursos; los recursos propios comprobados responden correctamente.

## Orden de implementación y validación

1. Optimizar logo e imágenes con comparación visual, conservando originales.
2. Reducir Font Awesome manteniendo iconos, accesibilidad y botones.
3. Configurar compresión y caché en el servidor, con invalidación de recursos.
4. Revisar tipografía, dimensiones reservadas y cobertura CSS.
5. Ejecutar Lighthouse móvil cuando esté disponible: al menos tres ejecuciones por plantilla representativa y comparar la mediana en condiciones equivalentes. Registrar el elemento LCP antes de cambiar prioridades de imágenes o añadir precargas.
6. Contrastar con Core Web Vitals de usuarios reales en Search Console cuando haya datos suficientes. El impacto de INP no puede inferirse de las descargas HTTP ni de una puntuación Lighthouse.

No conviene añadir prioridad alta o precargas a todas las imágenes, cargar todo el CSS de forma asíncrona ni eliminar estilos sin validar: podría empeorar la carga o alterar el aspecto.

## Referencias

- Optimización de fuentes: https://web.dev/learn/performance/optimize-web-fonts
- Diagnóstico y optimización de LCP: https://web.dev/articles/optimize-lcp

## Optimizaciones implementadas en local — 16 de octubre de 2026

Se han aplicado las dos primeras actuaciones sobre recursos, pendientes de publicación y de medir en navegador:

| Recurso | Original | Optimizado |
|---|---:|---:|
| Logo | 126.918 B | 24.108 B (480 px) o 9.579 B (240 px), según pantalla |
| Recreación LED | 605.670 B | 82.160 B, JPEG con las mismas dimensiones y composición |
| Foto pequeña del bloque inicial | 85.096 B | 29.331 B, 420 px para presentación de unos 210 px |
| Iconos (CSS de librería + dos fuentes) | 279.775 B | 6.018 B de CSS local y los siete SVG disponibles |

La comparación de iconos utiliza la descarga de ambas fuentes como referencia; los SVG se solicitan según su uso. El total de estos recursos con el logo de 480 px pasa de 1.097.459 B a 141.617 B (aproximadamente un 87 % menos). No representa el peso completo de la página ni un porcentaje de mejora del tiempo de carga.

El logo mantiene transparencia y usa srcset con variantes de 240 y 480 px y dimensiones HTML explícitas. La recreación original no tiene transparencia, por lo que se ha utilizado JPEG compatible tras comparar visualmente el resultado. Los originales no se han eliminado ni sobrescrito. El marcado LocalBusiness conserva su referencia al logo original como identificador de marca; no es una imagen visible solicitada por ese marcado.

Font Awesome remoto se ha sustituido por los siete SVG originales de la versión 6.4.0, servidos localmente como máscaras CSS que heredan el color del texto. Se conserva la licencia en img/icons/LICENSE.txt y la atribución en los SVG. Los iconos decorativos están ocultos a lectores de pantalla; los textos y etiquetas accesibles de enlaces y controles se mantienen.

Validación realizada: comparación visual de archivos de imagen, transparencia del logo, sintaxis PHP, generación HTML de las ocho páginas, referencias a recursos e iconos, XML de los SVG, destinos de enlaces y coherencia del sitemap. No se ha podido realizar una comprobación visual del sitio renderizado ni medir Core Web Vitals en esta sesión. Compresión del servidor, caché y Google Fonts quedan pendientes.

## Compresión, caché y fuentes implementadas en local — 16 de octubre de 2026

Se ha preparado `.htaccess`, fuente WOFF2 local precargada, un CSS minificado por plantilla y recursos generados con huella de contenido. La configuración se probó mediante peticiones HTTP a Apache aislado, verificando gzip y las políticas de caché. Las ocho páginas superan las comprobaciones de PHP y referencias internas. Las instrucciones de publicación y mantenimiento están en `docs/publicar-rendimiento.md`. Estos cambios aún deben subirse al hosting; no hay una nueva medición de PageSpeed posterior a ellos.
