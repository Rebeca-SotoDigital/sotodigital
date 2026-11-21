# SEO · 16/10/2026

Se conservan las URLs PHP, sitemap, robots, canonical y datos de negocio existentes.
Se añaden contexto visible al H1 de portada, encabezados descriptivos de servicios,
Open Graph por página y JSON-LD Service y BreadcrumbList en servicios.
Las redirecciones 301 solo se aplican al dominio de producción: HTTPS sin www e index.php hacia /.
El hosting debe admitir mod_rewrite y comunicar correctamente HTTPS a Apache.

Publicar los PHP modificados, .htaccess, CSS fuente y generado e includes/asset-manifest.php.
Subir los recursos antes de las plantillas. No eliminar las versiones anteriores.
Tras publicar: comprobar redirecciones, probar resultados enriquecidos, enviar sitemap
https://soto-digital.com/sitemap.xml en Search Console y solicitar inspección de las páginas principales.
No se han modificado Search Console ni el Perfil de Empresa; no se ha desplegado.
