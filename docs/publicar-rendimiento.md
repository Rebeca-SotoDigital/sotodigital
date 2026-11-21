# Publicar los cambios de rendimiento

Los cambios están comprobados en una instancia aislada de Apache local. No se ha modificado el hosting de producción.

## Archivos que se deben publicar juntos

- `.htaccess` de la raíz (es un archivo oculto; comprobar que el cliente FTP lo muestra y lo sube).
- `includes/header.php`, `includes/footer.php` e `includes/asset-manifest.php`.
- Todos los archivos de `css/`, `js/` y `fonts/`, incluidos los nombres con huella de contenido.
- Los archivos de imágenes e iconos de la optimización anterior, si no se publicaron aún.

Subir primero los recursos y el manifiesto, y después las plantillas. Conservar las versiones anteriores de los recursos durante el despliegue para que páginas ya abiertas sigan funcionando. Si existe caché HTML en el hosting/CDN, purgarla después de publicar.

## Qué cambia

- Una hoja CSS minificada por página: incluye estilos base, iconos, rediseño, fuente y, en las páginas de servicios, sus estilos específicos.
- Montserrat variable WOFF2 local (35.508 bytes) para pesos 400–800, con caracteres latinos españoles y `font-display: swap`. Se conserva SIL OFL en `fonts/OFL.txt`.
- Precarga de esa misma fuente con `crossorigin` y sin conexiones a Google Fonts.
- Scripts diferidos al final del documento; mantienen el orden y sus eventos DOMContentLoaded.
- Compresión gzip de texto, sin recomprimir imágenes o WOFF2.
- Caché de un año e `immutable` para los CSS, JS y WOFF2 cuyos nombres contienen la huella de contenido.
- Caché de 30 días para estáticos sin huella. Cuando se sustituya una imagen en el futuro, usar un nombre nuevo para no reutilizar una URL que aún pueda estar en caché.
- Revalidación (`no-cache`) de páginas, robots.txt y sitemap. No significa prohibir el almacenamiento; obliga a comprobar la versión antes de reutilizarla.

## Futuras ediciones

Editar los CSS y JS fuente (`style.css`, `redesign.css`, `icons.css`, `services.css`, `main.js`, `cookies.js`), y ejecutar desde la raíz:

```sh
python3 tools/build-assets.py
```

El script genera nombres nuevos si cambia el contenido y actualiza `includes/asset-manifest.php`. Publicar los resultados y el manifiesto juntos. No editar los archivos generados con huella directamente. Los SVG utilizados por los iconos llevan además una versión de contenido dentro del CSS generado.

## Comprobaciones realizadas

- Las ocho páginas generan HTML con PHP sin errores y mantienen enlaces, anclas, metadatos y sitemap coherentes.
- Las URLs internas del CSS generado existen, incluidos SVG y fuente.
- Apache devuelve 200 para la portada, página de servicio y recursos comprobados.
- Respuestas gzip verificadas en HTML, CSS, JS, SVG y XML.
- Cabeceras de caché comprobadas en documentos, CSS, JS, imágenes y fuente.
- CSS principal: 8.289 bytes de transferencia comprimida; CSS de servicios: 8.700; HTML portada: 6.219; JS principal: 1.168. Son mediciones locales, no tiempos de carga móviles ni resultados de PageSpeed.

No se ha podido revisar visualmente en navegador ni volver a medir Core Web Vitals en esta sesión. Después de publicar, verificar menú móvil, cookies y páginas de servicios, y repetir varias ejecuciones de PageSpeed en condiciones equivalentes. El hosting debe permitir `.htaccess` y disponer de los módulos necesarios; las secciones opcionales se omiten si no están disponibles. No se promete una puntuación concreta.
