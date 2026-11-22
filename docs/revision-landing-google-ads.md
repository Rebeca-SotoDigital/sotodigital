# Revisión local de la landing de Google Ads

Fecha: 22/11/2026. Ruta: `/campanas/google-ads.php`. Variantes: `?v=empresas` y `?v=cantabria`.

## Ajustes realizados

- Mantener titular orientado a consultas y mostrar los tres beneficios del servicio en el bloque inicial, junto a la primera respuesta de máximo 2 días laborables.
- Explicar el alcance: búsquedas/público, anuncios/landing, presupuesto/medición y seguimiento. La creación o mejora de la landing se detalla en la propuesta; no se promete incluida automáticamente.
- Concretar el proceso: revisión gratuita de los datos, estrategia y propuesta con proyección orientativa cuando haya datos suficientes. Honorarios e inversión separados; sin ventas ni costes garantizados.
- Resolver dudas propias de Google Ads: campañas existentes, accesos, necesidad de landing, presupuesto, plazos y resultados. No pedir claves en el formulario.
- Conservar un único cuestionario de cinco pasos y el circuito Brevo/Claude ya probado. CTA inicial, cabecera, final y fijo usan `#propuesta`, sin recarga ni pérdida de variante o parámetros de la URL. Este ajuste de enlaces se comparte con las demás landings.
- Mantener la multicanalidad según necesidades y presupuesto. Omitir la pantalla LED en esta landing mientras falte confirmar disponibilidad.
- Conservar ocultos el vídeo, testimonios y casos de Google Ads: no hay material de ese servicio que permita publicarlos como prueba de resultados. Los proyectos web existentes no se presentan como resultados de publicidad.

## Verificación y límites

Suite automatizada de formulario, emails, Brevo, consentimiento y deduplicación correcta. Render PHP y HTTP locales: once landings y variantes de Google Ads con respuesta 200, recursos disponibles, formulario con endpoint correcto, un H1 y noindex en HTML/cabecera. Sin enlaces públicos a WhatsApp o teléfono ni sección vacía de vídeo/testimonios. No se ha enviado otro formulario ni activado campañas.

Noindex se mantiene y las campañas no se añaden al sitemap. Las URLs son públicas y compartibles: no se bloquean por referer ni por parámetros de Google Ads. El sitemap conserva las 14 páginas públicas.

Pendiente revisión visual por el equipo en móvil/escritorio: el agente no tiene acceso a una pestaña de navegador en esta sesión. Confirmar que el texto de cada anuncio coincide con su variante antes de invertir. También pendientes vídeo real/subtítulos, resultados y permisos verificables, validación definitiva de privacidad y configuración de producción.

## Para revisar

Abrir `http://localhost/sotodigital/campanas/google-ads.php`. Probar también las variantes `?v=empresas` y `?v=cantabria`. Los botones deben bajar al cuestionario sin recargar ni cambiar la variante. El móvil sigue siendo opcional. La mini-auditoría es una revisión inicial de los datos; no una promesa de informe independiente ni de entrega de propuesta completa en dos días.

Todo sigue en local; no se ha publicado en Arsys. La aprobación de esta revisión de contenido no sustituye los pendientes del aviso legal de la sociedad en constitución.
