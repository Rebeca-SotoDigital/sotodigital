# Pendientes de Soto Digital

Actualizado: **27 de noviembre de 2026**.

Lista operativa vigente. Un push a GitHub no confirma la publicación en Arsys. No se dan por terminadas pruebas en producción sin evidencia o confirmación del equipo.

## 1. WhatsApp: puesta en marcha del agente

El código del agente, webhook y ejecutor HTTPS protegido está incorporado en `main`. Los envíos reales requieren activación explícita; la presencia del código no confirma que el servicio esté operativo.

- [ ] Confirmar despliegue en Arsys y configuración privada de Meta, Anthropic, autenticación del ejecutor y directorios de conversaciones, cola y leads fuera de la carpeta pública. No subir secretos a GitHub.
- [ ] Verificar número y permisos de WhatsApp Business, webhook y suscripción a los eventos necesarios.
- [ ] Configurar y probar la ejecución periódica del worker de WhatsApp; comprobar sus avisos de fallo. No confundirlo con el worker del formulario ya operativo.
- [ ] Probar una conversación real completa: recepción, respuesta, recogida de datos, confirmación de propuesta y guardado sin duplicados. Comprobar también reintentos y recuperación de errores.
- [ ] Definir y comprobar cómo recibe el equipo el lead de WhatsApp y si debe sincronizarse con Brevo. El guardado propio de WhatsApp no demuestra una integración con Brevo.
- [ ] Revisar información de privacidad, aviso de asistente de IA, conservación y acceso a conversaciones, así como cobertura de las copias y borrado.
- [ ] Confirmar en producción dónde se muestra el botón de WhatsApp y su destino; mantener diferenciada su medición del formulario y no contar un clic como lead confirmado.

## 2. ChatGPT Ads: publicar y preparar el servicio

Página pública `/chatgpt-ads.php` y landing `/campanas/chatgpt-ads.php` creadas. Footer, servicios relacionados y descripción de Campañas actualizados. Código subido a GitHub; publicación en Arsys aún sin confirmar.

- [ ] Subir los cambios a Arsys y revisar página pública, landing y enlaces en móvil y escritorio.
- [ ] Comprobar `SERVICIO_INTERES` en Brevo: si es categoría, añadir «ChatGPT Ads». Probar una solicitud real y verificar servicio, puntuación PHP, resumen IA y correos.
- [ ] Antes de gestionar campañas, comprobar acceso a Ads Manager, cuenta y permisos del cliente, requisitos vigentes, honorarios, presupuesto y plan de medición.
- [ ] Concretar la oferta de prueba piloto: entregables, duración, criterios para continuar o detener y alcance del seguimiento. No hay campaña activada ni resultados propios acreditados.
- [ ] Comprobar el sitemap publicado con 23 páginas públicas, incluida `/chatgpt-ads.php`; la landing de campañas mantiene noindex y queda fuera del sitemap. Solicitar indexación de la página pública tras publicarla.

## 3. Google Ads: campañas propias de Soto Digital

- [ ] Completar facturación y datos del anunciante.
- [ ] Definir presupuesto mensual de anuncios propios, pendiente de respuesta del equipo.
- [ ] Configurar y comprobar la conversión de solicitud válida en Google Ads. GA4 ya recibe `generate_lead` como evento clave.
- [ ] Preparar palabras clave con el Planificador, negativas, anuncios, zonas y presupuesto por campaña.

Preferencias confirmadas: todos los servicios, toda España, sin especialización por sector ni exclusiones comerciales. Prioridades de inversión por decidir según recursos y demanda.

## 4. Vídeos y testimonios

- [ ] Grabar vídeos explicando los servicios, preferiblemente entrevistas, y editar/subtitular.
- [ ] Recopilar testimonios de TecniRefinish y Cantabria Kebab con permiso de publicación.
- [ ] Incorporar resultados verificables y autorizados cuando existan. No inventar cifras ni atribuir resultados de campañas a proyectos sin evidencia.

No es necesario hacer nuevas fotos para mantener publicada la sección del equipo. El mensaje de trato directo ya está incorporado y subido a GitHub; su publicación en Arsys está pendiente de confirmar.

## 5. Pantalla LED

- [ ] Confirmar subida de los últimos textos y probar el recorrido de interés y su confirmación en producción.
- [ ] Resolver internamente financiación, disponibilidad, fecha, tarifas y condiciones de emisión antes de comercializar.

Preparado en local y GitHub: «Proyecto en preparación», formulario de interés específico y confirmación sin reserva ni pago. La necesidad de financiación no aparece en el texto público. Diseño y adaptación se concretan por separado.

## 6. Legal y privacidad

- [ ] Confirmar finalización de la constitución y completar datos registrales.
- [ ] Revisar proveedores y tratamientos definitivos, inventario de cookies y procedimiento de atención de derechos, incluyendo WhatsApp/IA y las futuras campañas.
- [ ] Ratificar o ajustar los 12 meses desde la última comunicación previstos para solicitudes sin contratación; definir también conservación de conversaciones de WhatsApp.
- [ ] Confirmar si Dealfront/Leadfeeder sigue desactivado.
- [ ] Si se decide enviar publicidad propia: preparar permiso opcional separado, registro y baja. La lectura de privacidad y la consulta por WhatsApp no implican alta en newsletters.

Datos confirmados: Soto Digital S.L., NIF B05897236, Polígono de Raos, Calle Galera 41, 39600 Maliaño, Cantabria. Derechos y privacidad: info@soto-digital.com. Últimos cambios legales anteriores confirmados subidos por el equipo. Instagram solo enlazado actualmente; futuras integraciones publicitarias pendientes de configurar.

## 7. Seguimiento SEO

- [ ] Entre el **3 y el 10 de diciembre de 2026**, comprobar en Search Console la indexación de `/blog/` y sus tres artículos. Solicitudes de indexación ya realizadas; no repetir diariamente.
- [ ] Comprobar después la indexación de la página pública de ChatGPT Ads cuando se publique y solicite.

No hay recordatorio automático creado. Google Business Profile sigue pospuesto: trabajo nacional por videollamada y visitas locales solo como posibilidad.

## Ya completado; no repetir

- Integración del formulario con Brevo e IA, correos, procesamiento periódico y `generate_lead` en GA4.
- Revisión previa de landings, protección .htaccess, consentimiento y copias diarias de leads del formulario con retención máxima de tres.
- Tres artículos del blog publicados; sitemap anterior de 22 URL enviado y cuatro indexaciones solicitadas. La nueva versión local tiene 23 URL.
- Organización de mantenimiento: Rebeca responsable principal y Jónatan suplente; avisos a info@soto-digital.com y rebeca@soto-digital.com. Revisar que los nuevos procesos de WhatsApp queden cubiertos por esa organización.

Los estados anteriores se conservan en [el histórico](docs/pendientes-historico-2026-11-26.md). Este documento sustituye las listas y preguntas duplicadas anteriores.

## Mejora de la landing de Google Ads — 27 de noviembre

- [x] Preparados en local los bloques de propuesta concreta, tipos de campaña y encaje, trato directo y seguimiento orientado a decisiones, conservando estilos y anchos existentes.
- No se incluyen casos ni resultados de gestión de campañas: el equipo confirma que todavía no ha gestionado campañas para clientes. El ejemplo visual no se ha incorporado al no haberse solicitado en esta ronda.
- [ ] Subir `includes/campaign-content.php` y `includes/campaign-page.php` a Arsys y revisar la landing en móvil y escritorio. Frecuencia de seguimiento, entregables y formatos finales se acuerdan en cada propuesta.

## Sitemap y robots — revisión del 27 de noviembre

- [x] Sitemap regenerado y validado: 23 URL públicas únicas; incluye Email marketing y ChatGPT Ads. Las landings de `/campanas/` mantienen noindex y no se incluyen.
- [x] Robots revisado: permite el rastreo y referencia `https://soto-digital.com/sitemap.xml`; no bloquea la lectura de las directivas noindex.
- [x] Email marketing ya dispone de servicio en home/footer, página pública `/email-marketing.php` y landing `/campanas/email-marketing.php`. No requiere crear otra página.
- [ ] Subir las últimas versiones de `sitemap.xml`, `robots.txt` y los cambios de la landing de Google Ads a Arsys. Comprobar el sitemap publicado antes de reenviarlo en Search Console; no se confirma despliegue por actualizar los archivos locales.

## Contenido ampliado — 27 de noviembre

- [x] Recorrido multicanal explicado en Campañas y landings: descubrimiento, comprensión, contacto y seguimiento.
- [x] Meta Ads ampliado en página y landing con formatos ilustrativos, materiales, objetivos y atención de consultas.
- [x] Email marketing ampliado en página y landing con bienvenida, atención de solicitudes y recuperación de interés, diferenciando suscripción y consulta. No se activan automatizaciones reales por publicar estos ejemplos.
- [x] Equipo: perspectiva de trabajo con clientes y negocios propios y atención nacional, sin inventar funciones individuales ni motivos de fundación.
- [ ] Subir los archivos compartidos y `publicidad-digital.php`, y revisar móvil/escritorio. Si se desea una historia fundacional y funciones concretas de cada persona, falta que el equipo aporte esos datos.

## ChatGPT Ads — propuesta ampliada

- [x] Página pública y landing ampliadas en local: encaje, propuesta concreta, preparación de la web, responsabilidades, medición y criterios de continuidad. Aclarada independencia de respuestas y privacidad de conversaciones.
- [ ] Subir `chatgpt-ads.php`, `includes/campaign-services.php` y `includes/service-content-blocks.php` junto a las plantillas compartidas pendientes; revisar visualmente ambas páginas. No se han activado campañas, píxeles ni nuevas integraciones.

## SEO de ChatGPT Ads — 27 de noviembre

- [x] H1 de la página pública centrado en agencia/publicidad/ChatGPT Ads, enlace contextual desde Campañas y artículo de apoyo con enlaces de ida y vuelta. Firma Soto Digital y sin fechas visibles, como el resto del blog. Fuentes oficiales contrastadas.
- [x] Nuevo artículo local: `/blog/publicidad-chatgpt-antes-de-invertir.php`. Sitemap regenerado con 24 URL públicas; la landing de campañas sigue excluida por noindex.
- [ ] Subir página, artículo, datos/plantillas compartidas y sitemap a Arsys; comprobar canonical/indexabilidad en producción y solicitar indexación de las dos URL nuevas.
- [ ] Contrastar demanda de palabras clave en herramientas reales; no hay volúmenes ni posiciones acreditadas.
