# Pendientes de Soto Digital

Actualizado: 21 de noviembre de 2026.

> **Cambio de criterio vigente:** captación exclusivamente mediante formulario. Se han retirado teléfonos públicos, el bloque Contacto del footer, los datos y alternativas de «Hagamos que pase algo» y los accesos a WhatsApp. Los antiguos apartados sobre WhatsApp describen una fase anterior y no deben reactivarse sin una nueva indicación.


Este documento reúne los pasos para terminar, activar y publicar los servicios nuevos, las landings de Google Ads, la captación mediante formulario y la identificación empresarial. Las casillas vacías son tareas pendientes. Los responsables propuestos son **Soto Digital** —decisiones, contenidos y cuentas— y **Desarrollo/hosting** —configuración, código, pruebas y publicación—. Una misma persona puede asumir ambos roles.


## Pendientes vigentes tras la auditoría

**Confirmación del solicitante resuelta:** el usuario confirmó ai@soto-digital.com. Corregida la dirección de esa solicitud (con historial privado en su payload), contacto sincronizado y confirmación entregada según Brevo el 21/11/2026 a las 18:09:06 Europe/Madrid. El aviso al equipo ya estaba entregado a ads@soto-digital.com y no se duplicó. El rebote anterior fue por ia@soto-digital.com inexistente, no por ausencia de envío.

**Estado real de Brevo comprobado el 21/11/2026:** clave aceptada, IP autorizada y lista configurada válida. Creados y validados PUNTUACION (número, tipo API float), POTENCIAL, VERSION_PUNTUACION, SIGUIENTE_PASO_IA y ESTADO_IA (texto). Remitente ads@soto-digital.com activo; dominio soto-digital.com verificado y autenticado. Brevo confirma ambos DKIM, código TXT y DMARC; DMARC también coincide en los dos servidores DNS autoritativos. `brevo-check` devuelve ok=true. Prueba real posterior autorizada completada: contacto técnico ID 101 creado, puntuación PHP 9/10 caliente y dos emails entregados a ads@soto-digital.com (17:05 Europe/Madrid). No se sobrescribieron contactos existentes. Detalle en `docs/prueba-real-brevo.md`. Formulario local y worker local comprobados; pendiente configuración y prueba del circuito en Arsys. No confundir la cuenta Brevo configurada con todo el circuito de producción activado.

Este listado sustituye los apartados históricos inferiores. Implementación y pruebas locales realizadas; **no se ha publicado ni verificado ninguna cuenta real**. Informe y evidencias: `docs/auditoria-2026-11.md`.

1. **Arsys y lanzamiento (equipo/hosting):** comprobar PHP y extensiones PDO SQLite/cURL/mbstring, Apache/rewrite/headers, HTTPS, rutas desde raíz, carpeta privada y permisos. Configurar cron worker cada 5 minutos, copia diaria, purga y monitor externo protegido con aviso independiente. Probar restauración y preparar rollback antes de activar anuncios.
2. **Brevo:** cuenta, lista, atributos obligatorios, remitente y dominio ya validados con la API real. Alta de contacto y ambos emails probados realmente; entrega confirmada por Brevo. Falta el recorrido completo desde el formulario y la ejecución automática del worker en el hosting. Opcionales aún ausentes: GBRAID, WBRAID, UTM_SOURCE, UTM_MEDIUM, SERVICIO, ID_SOLICITUD y VERSION_PRIVACIDAD; se conservan localmente. Sin suscripciones automáticas ni cambios en contactos existentes.
3. **Formulario local/Turnstile: completado.** Claves reales cargadas por Apache, almacenamiento privado preparado y formulario habilitado. Prueba negativa 422 sin guardar; envío válido confirmado el 21/11/2026 a las 17:44:40: POST 200, gracias 200 y un lead SQLite con 5/10 templado. Se corrigieron el fallback del limitador y los mensajes de validación. Worker ejecutado manualmente tras petición del usuario: sincronización real verificada en contacto 101, 5/10 templado y lista correcta; ambos emails aceptados. Worker local automático instalado mediante LaunchAgent cada cinco minutos: primera ejecución exitosa y cola vacía. Pendiente cron equivalente en Arsys; el Mac debe estar despierto y la sesión abierta. Ver `docs/worker-local.md`. Ver `docs/prueba-formulario-local.md`.
4. **Claude (equipo):** cuenta, clave, facturación/límite y acceso al modelo; verificar resumen y recuperación. Solo recibe categorías y puntuación, sin datos personales ni texto libre.
5. **Medición (equipo/desarrollo):** IDs GA4/Ads y label del formulario, Tag Assistant, aceptar/rechazar/retirar permisos y conversión única. Elegir una sola conversión principal para evitar duplicar etiqueta directa e importación GA4. Ads sin personalización. Revisar proveedores bloqueados y CSP report-only en dominio real antes de endurecerla.
6. **Privacidad (equipo):** datos registrales, contratos y transferencias de Arsys/Brevo/Cloudflare/Anthropic, inventario final de cookies/proveedores, política de copias y responsables de última comunicación/relación comercial. Revisar técnicamente y jurídicamente la configuración definitiva. Eliminación local a 12 meses implementada; hay que programarla y gestionar Brevo/copias. Newsletters siguen desactivadas.
7. **Oferta (equipo):** fijar alcance de mini-auditoría gratuita, plazo real de respuesta y precios si queréis publicarlos. No hay cifras inventadas. Completar `CAMPAIGN_RESPONSE_TIME` solo tras aprobarlo.
8. **LED (equipo):** confirmar si funciona, disponibilidad y datos verificables antes de venderla; mantener claramente identificada la recreación mientras corresponda.
9. **Confianza (equipo):** entregar fotos autorizadas de Jónatan y Rebeca y aprobar presentación; conseguir permisos de proyectos/logos, testimonios y resultados demostrables. Clientes destacados ocultos hasta autorización. No confundir entregables con mejoras medidas.
10. **Vídeos (equipo):** grabar entrevista de Google Ads primero; identificar quién interviene, explicar problema/proceso/auditoría sin promesas. Entregar vídeo autorizado, miniatura, subtítulos y alojamiento/MP4 optimizado. Después repetir para servicios anunciados; aprobar antes de activar bloques.
11. **SEO y contenido (equipo/desarrollo):** aprobar borradores `/google-ads.php`, `/meta-ads.php` y blog antes de retirar noindex y regenerar sitemap. Verificar Google Business Profile de Maliaño y validar esquema con herramienta de resultados enriquecidos al publicar.
12. **Campañas (equipo):** usar Planificador real, definir zonas, presupuesto, grupos por intención, palabras negativas y anuncios coherentes con landing/variante. Landings sin menú y noindex; no son un acceso privado exclusivo para clics Ads. No bloquear usuarios por ausencia de gclid.
13. **Accesibilidad/rendimiento (desarrollo):** resolver alternativa de envío sin JavaScript (Turnstile lo requiere actualmente); comprobar con tecnologías de apoyo reales. Revisar variación Lighthouse y repetir en Arsys con cuentas reales y sin llamadas de prueba. La prueba local no equivale a Core Web Vitals de usuarios.
14. **Prueba de producción controlada (equipo/desarrollo):** copia previa, despliegue conjunto, un lead propio con consentimiento y revisión de guardado, Brevo, IA, los dos emails, gracias/conversión, cron, monitor y restauración. Solo después activar publicidad.
15. **Opcional, posterior:** alta Dealfront bajo consentimiento; segmentos y pipeline Brevo (esqueleto de tratos desactivado, necesita IDs y revisión); importación offline solo diseñada. Propuestas, firma, pagos y freelancers fuera de esta fase.

**Resuelto en local:** tabla acordada 0–10 con históricos, móvil opcional, tokens y Turnstile diferidos, formulario en hero/servicios, variantes cerradas, cabeceras/404 privados/caché, copias/purga/health, consentimiento equilibrado con caducidad, eventos sin datos personales, eliminación de restos de chat, imágenes/SEO/sitemap y pruebas automatizadas. Los detalles inferiores son históricos y no deben usarse como checklist actual.

## 0. Cambios realizados el 21 de noviembre y próximos pasos inmediatos

### Implementado en local

- [x] Incorporar `ads@soto-digital.com` como contacto de publicidad y destino predeterminado de avisos.
- [x] Incorporar `ai@soto-digital.com` como contacto para temas de IA. Las cuentas de correo han sido comunicadas como configuradas por Soto Digital; no se ha verificado su recepción desde esta sesión.
- [x] Titular de la home orientado a consultas y oferta de mini-auditoría inicial gratuita, confirmada por Soto Digital.
- [x] Cuestionario guiado en home y landings: sector, objetivo, inversión, urgencia y contacto; web y contexto opcionales.
- [x] Endpoint propio `api/lead.php`, guardado privado antes de depender de proveedores y respuesta de éxito solo tras guardar un lead válido.
- [x] Puntuación PHP 0–10 y categorías caliente/templado/frio, con tabla acordada v1-acordada-2026-11-21 y versión guardada.
- [x] Claude limitado a resumen y siguiente paso, sin decidir la puntuación y sin recibir datos de contacto ni texto libre.
- [x] Integración Brevo para crear/actualizar contactos y gestionar los emails transaccionales; sustituye a Resend.
- [x] Cola y proceso privado para las APIs; fallos de IA/CRM no pierden la solicitud.
- [x] Límite persistente de cinco intentos por hora/IP, HMAC, CSRF, Turnstile y filtro silencioso de spam.
- [x] Consent Mode v2 básico: etiquetas bloqueadas antes de consentimiento; conversión de solicitud guardada y evento separado para WhatsApp.
- [x] Ejemplo de configuración privada fuera de la web y documentación de atributos Brevo.
- [x] FAQ de precio sin tarifas inventadas, web opcional y mini-auditoría sin compromiso.
- [x] Pruebas locales de backend y lógica JS con proveedores simulados. No equivalen a revisión visual ni pruebas reales de cuentas.

### Lo primero que falta para activarlo

1. Crear/recuperar Brevo y Anthropic API, configurar claves y presupuesto/límites de uso. No basta con tener una suscripción al chat de Claude.
2. En Brevo, crear la lista **Leads web**, los atributos de `docs/integracion-leads.md` y verificar remitente/dominio.
3. Configurar Turnstile, almacenamiento privado y `soto-config.php` fuera del directorio público.
4. Programar `php bin/campaign-leads.php retry`: sin ese proceso, los leads se guardan pero no se procesan las APIs ni los emails.
5. Adaptar la puntuación provisional a la tabla original, ya recibida en la conversación completa de Claude.
6. Configurar los IDs/labels reales de Google y comprobar conversiones con Tag Assistant.
7. Revisar en navegador y ejecutar una prueba controlada real de formulario, Brevo, emails e IA.
8. Completar los vídeos, testimonios/evidencias, privacidad definitiva y publicación.

**No hay precios definidos; los rangos del cuestionario son inversión orientativa, no tarifas. La mini-auditoría gratuita está aprobada, pero falta concretar alcance, entrega y plazo de respuesta. Los archivos HTML/PHP citados por Claude no se adjuntaron: se implementó la descripción sobre las páginas existentes.**

## 1. Estado real: qué está preparado y qué falta

| Bloque | Preparado en la copia local | Pendiente |
| --- | --- | --- |
| Email marketing y contenidos | Portada, páginas propias, enlaces y sitemap | Revisión final y publicación |
| SEO y SEM | Resumen de ventajas, página propia, enlaces y sitemap | Revisión final y publicación |
| Landings de campañas | Una principal y diez por servicio, textos y diseño adaptable | Revisión visual, vídeos, evidencias y publicación |
| Google Ads | Landing específica y semillas de palabras clave | Cuenta, estudio en el Planificador, presupuesto, anuncios y lanzamiento |
| Casos | Dos casos anónimos basados en trabajos existentes | Situación inicial real, origen del cliente, métricas y permiso para mostrar clientes |
| Vídeos | Sección preparada y oculta mientras no haya archivo | Grabar, editar, subtitular e incorporar |
| Formulario | Cuestionario de cinco pasos, URL opcional, antispam y guardado privado | Turnstile, almacenamiento y prueba real; sin configuración sigue deshabilitado |
| Página de gracias | Preparada; solo accesible tras una solicitud recibida | Prueba completa con envío real |
| Emails | Integración transaccional Brevo y dos mensajes preparados | Cuenta, remitente/DNS, clave, cron y prueba real |
| Identificación empresarial | Integración Leadfeeder/Dealfront con consentimiento específico | Cuenta, configuración, textos definitivos y activación |
| IA para solicitudes | Puntuación PHP y resumen/siguiente paso con Claude implementados | Aprobar reglas, clave API, límites, cron y prueba real |
| CRM | Upsert de contactos en Brevo implementado | Lista Leads web, atributos, clave y prueba real |
| IA para empresas visitantes | No desarrollada | Integración distinta de las solicitudes; decidir alcance si se desea |
| Medición | Consent Mode v2 básico, generate_lead, conversión Ads y WhatsApp implementados | IDs/labels, Tag Assistant y validación real |

**No se ha publicado ninguno de estos cambios durante esta conversación, creado cuentas externas, contratado planes ni lanzado campañas.** Las comprobaciones realizadas han sido locales. No hay pruebas de entrega real de emails, identificación de empresas ni conversiones reales. No se ha acreditado una tasa de conversión de Ads&Roll ni se ha copiado su diseño.

## 2. Orden recomendado para completarlo

1. Confirmar servicios, zonas, objetivos y responsables.
2. Reunir acceso al hosting y DNS; crear o recuperar las cuentas necesarias.
3. Configurar almacenamiento, antispam y correo para que el formulario funcione.
4. Completar los casos existentes y grabar los vídeos, empezando por Google Ads.
5. Revisar el diseño y los textos de todas las landings.
6. Preparar la medición y validar las palabras clave en la cuenta real de Google Ads.
7. Completar privacidad y cookies con la configuración definitiva.
8. Publicar sin tráfico de pago y hacer una prueba completa controlada.
9. Activar identificación empresarial y comprobar su funcionamiento.
10. Lanzar las campañas elegidas con presupuesto aprobado y seguimiento.
11. Activar y validar el procesamiento en Brevo/Claude con la cola; valorar después ampliaciones para empresas visitantes.

**No activar inversión publicitaria hasta que una solicitud se guarde, llegue al equipo y pueda medirse correctamente.** La identificación empresarial y la IA pueden activarse por separado; no son requisitos para recibir formularios.

## 3. Confirmar la oferta y el objetivo de cada landing

**Responsable: Soto Digital.**

- [ ] Confirmar qué servicios se ofrecen y se pueden atender ahora, y cuáles tendrán campaña al inicio.
- [ ] Definir por servicio: cliente ideal, necesidad principal, alcance, entregables y diferencias respecto a otras agencias.
- [ ] Acordar zonas de captación: Cantabria, España u otras. La pantalla LED tiene una ubicación concreta aunque el anunciante pueda estar fuera.
- [ ] Separar honorarios de gestión y presupuesto publicitario en la propuesta comercial.
- [x] Confirmar mini-auditoría inicial gratuita, sin compromiso. No se promete una auditoría completa ilimitada.
- [ ] Concretar cómo se hará la auditoría, qué recibirá el cliente y en qué momento.
- [ ] Definir la propuesta estratégica: diagnóstico, objetivos, canales, acciones, inversión, indicadores y calendario.
- [ ] Definir una proyección con supuestos y escenarios. No prometer posiciones, ventas, contactos o rentabilidad garantizados.
- [ ] Asignar quién responde las solicitudes y un plazo interno realista. El texto público actual dice «lo antes posible»; no publicar un plazo más concreto sin capacidad para cumplirlo.

### Landings existentes

Las siguientes son rutas previstas en el dominio; su presencia en este documento no significa que ya estén publicadas.

| Página / servicio | Ruta |
| --- | --- |
| Principal multicanal | `/campanas/index.php` |
| Agencia de Google Ads | `/campanas/google-ads.php` |
| Diseño web | `/campanas/diseno-web.php` |
| Redes sociales | `/campanas/redes-sociales.php` |
| Branding y diseño gráfico | `/campanas/branding.php` |
| Instagram y Meta Ads | `/campanas/meta-ads.php` |
| Email marketing | `/campanas/email-marketing.php` |
| Marketing de contenidos | `/campanas/marketing-contenidos.php` |
| SEO | `/campanas/seo.php` |
| SEM | `/campanas/sem.php` |
| Pantalla LED en Raos | `/campanas/pantalla-led.php` |

- [ ] Revisar si Google Ads y SEM necesitan campañas separadas o compartir enfoque para evitar solapamientos.
- [ ] Confirmar si fotografía, vídeo y mantenimiento se ofrecerán como servicios independientes. Hoy están integrados en otras ofertas; no se han creado landings independientes para ellos.
- [ ] Mantener la recomendación multicanal vinculada al objetivo y presupuesto del cliente; concretar combinaciones útiles por servicio.

## 4. Reunir accesos y dar de alta las cuentas

**Responsable: Soto Digital, con apoyo de Desarrollo/hosting.**

Reutilizar las cuentas existentes cuando las haya. Las cuentas deben quedar a nombre del negocio, con recuperación y acceso controlado. No guardar contraseñas ni claves privadas en este documento o en archivos públicos de la web.

| Cuenta / acceso | Para qué se necesita | Qué hacer |
| --- | --- | --- |
| Hosting PHP | Publicar y guardar solicitudes | Localizar panel, SFTP/SSH, versión PHP y acceso a configuración |
| DNS de `soto-digital.com` | Verificar el envío de correo | Identificar quién gestiona la zona DNS y obtener acceso delegado |
| Buzones del equipo | Recibir y responder propuestas | Probar `ads@soto-digital.com`, `ai@soto-digital.com` y el contacto general |
| Google Ads | Planificador y campañas | Crear/recuperar cuenta; completar datos y facturación antes del lanzamiento |
| Cloudflare Turnstile | Protección antispam | Crear cuenta o usar la existente y registrar el widget |
| Brevo | Contactos y dos emails transaccionales | Crear/recuperar cuenta, lista Leads web, atributos, remitente y clave |
| Leadfeeder/Dealfront | Identificar empresas visitantes | Crear/recuperar cuenta y elegir un plan o prueba adecuado |
| Google Tag Manager, si se elige | Gestionar etiquetas de medición | Crear/recuperar contenedor e instalarlo con control de consentimiento |
| GA4, si se elige | Analizar comportamiento y resultados | Crear/recuperar propiedad y definir eventos; no es imprescindible si se mide directamente con Ads |
| CRM Brevo | Gestionar contactos | Código preparado; configurar cuenta/lista/atributos y permisos |
| Anthropic API (Claude) | Resumir solicitudes ya puntuadas | Crear/recuperar cuenta API y clave, revisar condiciones y límites |

- [ ] Revisar límites, costes actuales y condiciones antes de contratar. Este documento no presupone que ningún plan cubra gratis todas las funciones.
- [ ] Definir quién administra cada cuenta y activar las medidas de acceso que correspondan.

## 5. Activar el formulario y el guardado privado

**Responsable: Desarrollo/hosting. Depende de los accesos del apartado 4.**

### Hosting y base de datos

- [ ] Comprobar PHP compatible con el código y extensiones `curl`, `mbstring` y `PDO SQLite`.
- [ ] Comprobar HTTPS, sesiones PHP y certificados válidos.
- [ ] Verificar que PHP puede realizar conexiones HTTPS a Cloudflare, Brevo y Anthropic.
- [ ] Crear un directorio privado fuera del proyecto y del directorio público del hosting. No usar `public_html`, `htdocs` ni una carpeta descargable desde la web.
- [ ] Asignar escritura al usuario PHP y permisos restrictivos. Referencia: directorio `0700` y base de datos `0600`, ajustados al hosting.
- [ ] Configurar `CAMPAIGN_DATA_DIR` con la ruta absoluta real. La base SQLite se crea al utilizar el almacenamiento; no hace falta contratar MySQL para este código.
- [ ] Confirmar que no se puede descargar la base, sus copias ni los CSV desde una URL pública.
- [ ] Configurar copia de seguridad privada, acceso restringido y prueba de restauración.
- [ ] Confirmar que el espacio de almacenamiento y sus errores se supervisan.

### Campos y experiencia

- [ ] Revisar sector, objetivo, inversión orientativa, urgencia, nombre y apellidos, email, móvil y contexto opcional.
- [x] Permitir negocios sin web: URL opcional; el servidor calcula si tiene web sin un paso adicional.
- [ ] Decidir si el móvil seguirá siendo obligatorio y revisar formatos de teléfono de las zonas elegidas.
- [ ] Revisar los errores para campos vacíos, email incorrecto, URL incorrecta y mensajes demasiado largos.
- [ ] Verificar que el usuario conserva los datos necesarios al corregir un error; mejorar su recuperación si hace falta.
- [ ] Revisar accesibilidad: etiquetas, navegación con teclado, foco, contraste y errores comprensibles.

### Variables de entorno

Configurar en el servidor, sin incluir secretos en HTML, JavaScript ni repositorios:

| Variable | Contenido |
| --- | --- |
| `CAMPAIGN_DATA_DIR` | Directorio privado existente |
| `TURNSTILE_SITE_KEY` | Clave pública del widget |
| `TURNSTILE_SECRET_KEY` | Clave privada de verificación |
| `CAMPAIGN_ALLOWED_HOSTS` | Dominios permitidos, separados por coma |
| `BREVO_API_KEY` | Clave privada de Brevo, para contactos y email |
| `BREVO_LIST_ID` | ID numérico de la lista Leads web |
| `ANTHROPIC_API_KEY` | Clave privada de Claude API |
| `ANTHROPIC_MODEL` | Por defecto claude-haiku-5-5, configurable |
| `CAMPAIGN_RATE_SECRET` | Secreto HMAC opcional; si falta, se utiliza el secreto Turnstile |
| `SOTO_CONFIG_FILE` | Ruta absoluta del archivo privado soto-config.php, si se utiliza |
| `CAMPAIGN_MAIL_FROM` | Remitente autorizado del dominio verificado |
| `CAMPAIGN_MAIL_TO` | Por defecto `ads@soto-digital.com` |

- [ ] Confirmar que tanto PHP web como cron reciben la misma configuración. Copiar el ejemplo `docs/soto-config.example.php` fuera de la web; nunca rellenar claves reales dentro del proyecto.
- [ ] Verificar activación con almacenamiento y Turnstile válidos. Captura no depende de Claude/Brevo; sin claves o sin cron las integraciones quedan pendientes, no terminadas.
- [ ] Probar también una configuración incorrecta: tener variables no demuestra que las claves o permisos funcionen.

## 6. Configurar «no soy un robot» con Turnstile

**Responsables: Soto Digital y Desarrollo/hosting.**

- [ ] Crear/abrir la cuenta de Cloudflare y añadir un widget de Turnstile.
- [ ] Autorizar los hostnames de producción utilizados, por ejemplo `soto-digital.com` y `www.soto-digital.com` si ambos sirven páginas.
- [ ] Obtener la clave pública y la privada y configurarlas en las variables anteriores.
- [ ] Comprobar que `CAMPAIGN_ALLOWED_HOSTS` coincide con los dominios reales. Separar pruebas y producción.
- [ ] Probar el widget en móvil y escritorio y su verificación en el servidor. No basta con que aparezca una casilla en pantalla.
- [ ] Confirmar que se comprueban token, hostname y acción `proposal`.
- [ ] Probar token vacío, incorrecto, caducado o reutilizado y fallo de comunicación con Cloudflare.
- [ ] Revisar la presentación final: Turnstile puede verificar automáticamente o pedir interacción; no siempre muestra literalmente una casilla «no soy un robot». Si se exige esa apariencia exacta, decidir y adaptar la solución antes de publicar.

La integración ya existe, pero no tiene claves configuradas. [Guía oficial de Turnstile](https://developers.cloudflare.com/turnstile/get-started/).

## 7. Configurar Brevo: contactos y emails

**Responsables: Soto Digital, gestor del DNS y Desarrollo/hosting. Brevo sustituye al planteamiento anterior con Resend.**

- [ ] Crear/recuperar la cuenta Brevo y revisar los límites y coste del plan elegido.
- [ ] Crear la lista **Leads web**, exclusiva para solicitudes, y guardar su ID en BREVO_LIST_ID.
- [ ] Crear todos los atributos, con nombre y tipo exactos, de `docs/integracion-leads.md`. Incluye URGENCIA, PUNTUACION, POTENCIAL, datos de campaña y resumen/siguiente paso IA.
- [ ] Revisar que añadir un contacto a Leads web no active una newsletter ni una automatización promocional de la cuenta.
- [ ] Verificar el dominio/remitente para email transaccional con los registros que indique Brevo. Mantener los buzones actuales operativos y revisar DMARC.
- [ ] Configurar BREVO_API_KEY, CAMPAIGN_MAIL_FROM y CAMPAIGN_MAIL_TO. Destino y respuesta predeterminados: ads@soto-digital.com. El remitente también puede ser esa dirección si está autorizado en Brevo.
- [ ] Probar recepción y respuesta en ads@soto-digital.com y acceso al nuevo ai@soto-digital.com. Este último no recibe copia automática de leads.
- [ ] Aprobar los textos de aviso y agradecimiento implementados. El acuse confirma la mini-auditoría gratuita y contacto posterior por teléfono o email.
- [ ] Programar la cola de `tools/campaign-leads.php retry`; no se utiliza mail() de PHP.
- [ ] Probar entrega a una cuenta controlada y al equipo, incluyendo spam, rechazo y respuesta.
- [ ] Configurar supervisión de pendientes y revisar estados `sending` ambiguos en el panel del proveedor antes de reintentar.
- [ ] Comprobar que un contacto existente se actualiza sin alterar sus bloqueos de marketing. Las solicitudes siguen conservándose por separado en SQLite; Brevo refleja la más reciente.
- [ ] Revisar seguimiento de aperturas/clics y desactivar lo que no sea necesario.

Referencia de configuración y atributos: `docs/integracion-leads.md`. Los envíos reales, credenciales, lista, atributos y DNS **no se han probado en una cuenta**.

## 8. Organizar la gestión de los leads

**Responsable: Soto Digital, con apoyo técnico.**

- [x] Preparar Brevo como destino de contactos y buzón ads@ para avisos, con historial local privado.
- [ ] Configurar el trabajo del equipo dentro de la cuenta Brevo: permisos, estados, asignación y revisión. El código no crea un pipeline de oportunidades.
- [ ] Tener claro que hoy no existe un panel web para ver, editar y asignar leads. Hay guardado en SQLite y herramientas de servidor.
- [ ] Si se necesita un panel propio o pipeline comercial adicional, definirlo y desarrollarlo. La integración de contactos Brevo no equivale a un panel propio completo.
- [ ] Definir estados: nuevo, pendiente de contactar, contactado, propuesta enviada, ganado y descartado.
- [ ] Asignar una persona y fecha de siguiente acción a cada oportunidad.
- [ ] Registrar última comunicación para aplicar el plazo de conservación; ese seguimiento no está implementado en la base actual.
- [ ] Implementar o acordar la eliminación periódica de solicitudes vencidas y su gestión en backups. No existe todavía una tarea automática de limpieza.
- [ ] Definir cómo se atienden solicitudes de acceso, rectificación o supresión.

Herramientas existentes, solo para personal con acceso al servidor:

```sh
php bin/campaign-leads.php export
php bin/campaign-leads.php retry
php bin/campaign-leads.php status
```

- [ ] Preparar la exportación a un destino privado fuera de la web. El primer comando escribe CSV en la salida del terminal.
- [ ] Programar el worker cada cinco minutos con configuración privada y registro de salida. El bloqueo entre trabajadores ya está implementado; configurar y comprobar cron sigue pendiente.
- [ ] Revisar en Brevo los emails en estado `sending` antes de reponerlos en `pending`. Se detienen para evitar duplicados ante respuestas ambiguas; la idempotencia del proveedor no sustituye esta revisión.

## 9. Grabar los vídeos del equipo

**Responsable: Soto Digital. La grabación es un pendiente real: no hay vídeos disponibles en el proyecto.**

### Plan de grabación

- [ ] Elegir a la persona del equipo que explicará cada servicio y a quien hará las preguntas fuera de cámara.
- [ ] Priorizar Google Ads. Después grabar la presentación multicanal y los vídeos de los servicios que vayan a anunciarse.
- [ ] Preparar ejemplos propios y respuestas coherentes con lo que realmente se ofrece.
- [ ] Grabar en formato entrevista: una persona pregunta y otra responde de forma natural. La persona que pregunta no necesita salir en plano.
- [ ] Como propuesta de producción, preparar una versión horizontal de aproximadamente 60–120 segundos por landing. Ajustar duración a la claridad del contenido.
- [ ] Usar trípode o apoyo estable, fondo ordenado, buena luz frontal y micrófono cercano. Un móvil puede servir si la imagen y el audio son claros.
- [ ] Evitar música sin licencia y pantallas o documentos con datos de clientes.
- [ ] Tener autorización de las personas que aparezcan.
- [ ] Editar pausas, añadir identificación del equipo y revisar subtítulos manualmente.
- [ ] Conservar el archivo original y exportar una versión MP4 optimizada para web, compatible con móvil.

### Guion de entrevista para Google Ads

1. ¿Qué tipo de negocio puede beneficiarse de Google Ads?
2. ¿Qué miráis antes de recomendar que invierta?
3. ¿Cómo investigáis las búsquedas y elegís las palabras clave?
4. ¿Cómo decidís zonas, presupuesto y estructura de campaña?
5. ¿Qué papel tiene la landing después del clic?
6. ¿Cuándo conviene combinar Google Ads con SEO, redes, contenidos o email?
7. ¿Qué ocurre después de que una persona envíe la solicitud?
8. ¿Cómo planteáis la proyección y qué resultados no se pueden garantizar?

### Adaptación a otros servicios

- Web: qué debe entender un visitante y cómo facilitar el contacto.
- Redes: planificación, contenido propio y coordinación.
- Branding: identidad y coherencia en los materiales.
- Meta Ads: público, creatividad, objetivo y medición.
- Email: audiencia autorizada, contenido útil y frecuencia.
- Contenidos: preguntas del cliente, temas y formatos.
- SEO: revisión, mejoras y evolución progresiva.
- SEM: búsquedas, inversión y relación entre anuncio y destino.
- Pantalla LED: ubicación real, dimensiones y preparación del anuncio; no inventar alcance ni afluencia.
- Principal: cómo se coordinan los canales alrededor del negocio.

### Entrega e incorporación técnica

- [ ] Entregar el MP4 final y subtítulos reales en `.vtt` para cada servicio.
- [ ] Crear `img/videos/` y subir los archivos. Ejemplo: `img/videos/google-ads.mp4` y `img/videos/google-ads.vtt`.
- [ ] Añadir la ruta MP4 a `videos` en `includes/campaign-content.php`, usando la clave del servicio. Para la principal la clave es `estrategia-digital`.
- [ ] Preparar una portada si se quiere mejorar la presentación; el código actual no tiene una portada de vídeo específica configurada.
- [ ] Comprobar reproducción, sonido, subtítulos, controles y peso de descarga.
- [ ] Revisar si hace falta añadir una transcripción accesible junto al vídeo.

**La sección permanece oculta hasta incorporar un archivo real.** No hay un vídeo de muestra que sustituya al equipo.

## 10. Completar los casos de éxito y los clientes destacados

**Responsable: Soto Digital. Se parte únicamente de los trabajos existentes en la web, como has indicado.**

Actualmente se describen entregables de restauración y distribución técnica. No hay evidencia de incrementos de ventas, tráfico, leads o rentabilidad ni del canal por el que llegó cada cliente.

- [ ] Revisar los proyectos existentes y elegir los que realmente se pueden considerar casos de éxito.
- [x] Preparar borradores para pedir testimonios reales a TecniRefinish y Cantabria Kebab en `docs/testimonios-clientes.md`, sin enviarlos.
- [ ] Revisar y enviar esos mensajes por vuestro canal habitual; guardar respuestas y autorización.
- [x] Preparar la sección de testimonios, oculta hasta añadir citas auténticas aprobadas.
- [ ] Recuperar cómo llegó cada cliente a Soto Digital: recomendación, búsqueda, contacto directo u otro origen. Si no se conoce, no atribuirlo a un canal.
- [ ] Documentar situación inicial, necesidad, trabajo realizado y resultado final.
- [ ] Si existen métricas, aportar fuente, periodo, valores inicial/final y alcance de la intervención.
- [ ] Separar resultados atribuibles al trabajo de cambios causados por otros factores.
- [ ] Redactar las tarjetas sin nombre del cliente, centradas en situaciones con las que otro negocio pueda identificarse.
- [ ] Mantener resultados descriptivos si no hay cifras verificables. No convertir un entregable en un supuesto aumento de ventas.
- [ ] No presentar proyectos web como casos de resultados en Google Ads o Meta Ads si esas campañas no se realizaron o no están documentadas.
- [ ] Revisar las asociaciones de casos a servicios: que cada caso respalde el servicio donde se muestra.
- [ ] Confirmar permiso de uso de nombres/logotipos y que cumplen el criterio de éxito antes de mantenerlos en la franja inferior de la principal.
- [ ] Revisar Cantabria Kebab y TecniRefinish, actualmente configurados como clientes destacados. Estar publicados en la web no acredita por sí solo métricas de éxito ni permiso para cualquier nuevo uso.
- [ ] Si no se confirma ese criterio, retirar el destacado o mantenerlo como proyecto realizado, sin etiquetarlo como éxito comercial.
- [ ] Separar clientes externos de proyectos propios del grupo.
- [ ] Actualizar `includes/campaign-content.php` con el material aprobado.

Ficha que hay que completar por caso:

| Dato | Qué aportar |
| --- | --- |
| Sector | Actividad del negocio |
| Punto de partida | Problema y situación anterior reales |
| Origen | Cómo contactó con Soto Digital, si se conoce |
| Trabajo | Servicios prestados y acciones concretas |
| Periodo | Fechas y duración |
| Resultado | Cambio o entregable comprobable |
| Evidencia | Capturas, informes, URLs o documentación |
| Publicación | Qué nombre, logo, cifras o materiales se autoriza a mostrar |

## 11. Revisar diseño y conversión de las landings

**Responsables: Soto Digital y Desarrollo.**

- [ ] Revisar visualmente cada landing en móvil y escritorio. No se ha completado esa revisión en navegador.
- [ ] Comprobar lectura del titular, ventajas y llamada a la acción antes de hacer scroll.
- [ ] Confirmar que anuncio, búsqueda y landing hablan del mismo servicio.
- [ ] Revisar formularios, teclado móvil, errores, botones, anclas y enlaces legales.
- [ ] Comprobar que el vídeo y el antispam no generan desbordamiento horizontal.
- [ ] Probar accesibilidad básica y carga con conexión móvil.
- [ ] Revisar tiempos de carga y peso de los vídeos; medir rendimiento después de publicarlos.
- [ ] Contrastar la referencia Ads&Roll manualmente si se quiere afinar el enfoque. No fue accesible para revisión en la sesión anterior.
- [ ] No afirmar que el diseño tiene una tasa alta de conversión sin datos propios.
- [ ] Planificar pruebas de mensajes o formularios cuando haya tráfico suficiente; no declarar un ganador con unas pocas visitas.

## 12. Preparar Google Ads y el Planificador de palabras clave

**Responsable: persona que gestione las campañas, con aprobación de Soto Digital.**

### Investigación real pendiente

- [ ] Acceder a Google Ads y al Planificador de palabras clave.
- [ ] Investigar cada servicio con las semillas de `docs/landings-google-ads.md` y su landing.
- [ ] Seleccionar zona, idioma y red adecuados al servicio.
- [ ] Guardar fecha, segmentación, búsquedas estimadas, competencia y rangos de puja.
- [ ] Distinguir búsquedas para contratar de búsquedas de cursos, empleo, información o herramientas.
- [ ] Elegir palabras y concordancias iniciales; revisar posibles negativas por grupo.
- [ ] Evitar negativas generales que bloqueen un servicio que sí se ofrece.
- [ ] Preparar escenarios de presupuesto y coste por lead con supuestos explícitos. Las previsiones no son garantías.
- [ ] Aprobar inversión diaria/mensual y separar coste publicitario de honorarios.

**Hoy solo hay semillas propuestas; no se ha realizado un estudio con volúmenes y CPC reales de una cuenta.** [Ayuda oficial del Planificador](https://support.google.com/google-ads/answer/7337243?hl=es).

### Construcción de campañas

- [ ] Completar los datos, verificaciones y facturación que solicite la cuenta.
- [ ] Elegir los servicios de la primera fase según capacidad de atenderlos y presupuesto.
- [ ] Crear grupos con intención clara y su URL final correspondiente.
- [ ] Redactar anuncios y recursos coherentes con la oferta real; no inventar certificaciones, garantías ni cifras.
- [ ] Revisar ubicación, idioma, horarios, dispositivos y redes activadas.
- [ ] Configurar presupuesto y estrategia de puja adecuados a los datos disponibles.
- [ ] Evitar que grupos de SEO, SEM y Google Ads compitan sin una intención diferenciada.
- [ ] Revisar políticas y aprobación de destinos antes de invertir.
- [ ] Dejar campañas pausadas hasta superar las pruebas del formulario y medición.

### Visibilidad solo desde anuncios

- [ ] Confirmar que se acepta la solución implementada: páginas fuera del menú público y del sitemap, con `noindex`.
- [ ] Mantener las landings disponibles para las revisiones de Google Ads y para quienes reciban el enlace.
- [ ] No bloquear por `Referer`, `gclid` o UTM: no prueban quién hizo clic y pueden faltar.
- [ ] No añadir un bloqueo en `robots.txt` que impida leer el `noindex`.
- [ ] Comprobar que las páginas públicas normales siguen pudiendo indexarse.

**Un enlace de anuncio se puede copiar y compartir. No se puede garantizar acceso exclusivo de quienes hayan hecho una búsqueda o clic en Google Ads con esta solución.** Si se requiere acceso privado, hay que redefinir el requisito y evaluar su compatibilidad con los anuncios.

## 13. Activar y validar medición de conversiones

**Implementado en local:** Consent Mode v2 básico, cuatro señales denegadas por defecto, carga de Google condicionada por categoría, generación de eventos solo después de guardar, deduplicación del recibo y evento independiente de WhatsApp. Personalización publicitaria siempre denegada. Las preferencias antiguas requieren una nueva elección (versión 3).

- [x] Implementar `generate_lead` para GA4, conversión directa Ads y WhatsApp como acción distinta.
- [x] Evitar conversión por clic en Enviar, spam descartado o simple recarga de gracias.
- [x] No enviar nombre, email, móvil, mensaje, puntuación ni contenido de WhatsApp en los eventos.
- [x] Mantener el origen recibido durante 30 minutos en la sesión al navegar entre landings.
- [ ] Crear/recuperar propiedad GA4 y cuenta Google Ads que se usarán realmente.
- [ ] Configurar GOOGLE_ANALYTICS_ID (G-...), GOOGLE_ADS_ID (AW-...), GOOGLE_ADS_LEAD_LABEL.
- [ ] Revisar la conversión del formulario como objetivo vigente; no activar medición de WhatsApp mientras no existan sus accesos públicos.
- [ ] Elegir una sola fuente principal por conversión: no importar además generate_lead si ya cuenta la etiqueta directa Ads.
- [ ] No instalar otro contenedor/etiqueta duplicado. La implementación actual utiliza gtag directamente y no requiere GTM.
- [ ] Validar con Tag Assistant consentimiento antes de carga, aceptación por categoría, rechazo y retirada.
- [ ] Verificar clic real, atribución, redirecciones y conversión registrada una sola vez.
- [ ] Comparar con solicitudes guardadas: sin consentimiento es normal que el modo básico no mida todas.

No hay IDs/labels reales configurados ni prueba de recepción en una cuenta. La integración está desarrollada, no activada.

## 14. Activar identificación empresarial con Leadfeeder/Dealfront

**Responsables: Soto Digital y Desarrollo. Independiente del formulario.**

- [ ] Crear/recuperar cuenta y revisar plan, límites, exportación, integraciones, duración de la prueba y coste antes de contratar.
- [ ] Registrar `soto-digital.com` y confirmar los hostnames que se quieren analizar.
- [ ] Obtener el código en Settings > Company > Website Tracker > Tracker script, o la sección equivalente de la cuenta.
- [ ] Extraer la URL pública del rastreador y configurar `LEADFEEDER_TRACKER_URL` en el servidor. No es una contraseña ni una clave API.
- [ ] No pegar el snippet además de la integración existente: produciría una carga duplicada y podría eludir el control del sitio.
- [ ] Confirmar el modo de consentimiento elegido. El código actual bloquea el script completo hasta aceptar «Identificación empresarial». Si se utiliza también el modo de consentimiento del proveedor, adaptar su señal de aceptación para que ambos funcionen juntos.
- [ ] Excluir el tráfico interno y de pruebas y revisar filtros del proveedor.
- [ ] Desactivar funciones de identificación personal o captura de formularios que no se hayan acordado.
- [ ] Completar las políticas con proveedor, datos, cookies, duraciones y condiciones reales antes de activar.
- [ ] Probar sin decidir, rechazar, aceptar solo otras categorías, aceptar identificación empresarial y retirar permiso.
- [ ] Confirmar que sin su permiso específico no hay peticiones al proveedor y, al aceptarlo, solo se carga una vez.
- [ ] Comprobar recepción en el panel con tráfico de prueba adecuado. Una conexión doméstica o móvil puede no identificar una empresa.
- [ ] Elegir qué empresas guardar y cómo evitar duplicados.
- [ ] Evaluar la cobertura real de la prueba antes de asumir el coste continuado.

**Identifica algunas empresas, no a cada visitante ni a la persona concreta.** Guardar empresas en el panel no crea automáticamente contactos personales en la base del formulario. Retirar cookies tampoco borra por sí solo el historial enviado: hay que gestionarlo en el proveedor.

[Instalación oficial de Leadfeeder](https://help.leadfeeder.com/en/articles/13921717-installing-the-leadfeeder-tracking-script-directly-on-your-website). Detalles locales en `docs/identificacion-empresarial.md`.

## 15. Activar Claude y aprobar la puntuación

**Implementado en local para las solicitudes del cuestionario.** PHP calcula puntuación y categoría; Claude únicamente propone resumen y siguiente paso para revisión humana. La IA sobre visitas empresariales de Leadfeeder es otra integración y sigue sin desarrollarse.

- [x] Guardar puntuación 0–10, categoría y versión de reglas junto a cada lead.
- [x] Umbrales: caliente 7–10, templado 4–6 y frio 0–3.
- [x] Limitar Claude a datos estructurados sin contacto, URL, texto libre, IP ni identificadores de campaña.
- [x] Limitar intentos de IA y conservar el lead y los demás procesos ante fallos.
- [ ] Aprobar o sustituir la tabla provisional de `includes/campaign-questionnaire.php`. La tabla original ya se recibió en la conversación completa; está pendiente adaptar las reglas y los rangos de respuesta. Detalle de la versión todavía provisional en `docs/integracion-leads.md`.
- [ ] Crear/recuperar cuenta Anthropic API, configurar facturación/límites y ANTHROPIC_API_KEY. La cuenta del chat no implica tener API configurada.
- [ ] Confirmar acceso al modelo configurado, por defecto claude-haiku-5-5, y ajustar ANTHROPIC_MODEL si hace falta.
- [ ] Revisar condiciones, conservación y garantías de los datos enviados, aunque no incluyan contacto directo.
- [ ] Programar la cola y probar resumen, límites, respuestas inválidas y caída de la API.
- [ ] Comprobar que un fallo deja resumen no disponible pero no pierde el lead ni su puntuación y permite continuar Brevo/emails.
- [ ] Revisar resultados humanos y calidad de las reglas con datos reales antes de utilizarlos para priorizar de forma habitual.
- [ ] Valorar por separado si se quiere conectar Leadfeeder a IA y CRM. No confundir empresa visitante con persona identificada.

No se envían propuestas ni se rechazan clientes automáticamente en función de la puntuación.

## 16. Completar privacidad, cookies y condiciones reales

**Responsable: Soto Digital con asesoramiento adecuado y apoyo técnico.**

Los textos preparados contienen apartados pendientes de concretar antes de activar proveedores. No son una validación jurídica definitiva.

- [ ] Confirmar identidad legal completa del responsable, datos de contacto y coherencia entre aviso legal, privacidad y formularios.
- [ ] Documentar finalidades y bases aplicables al formulario, medición e identificación empresarial según la configuración final.
- [ ] Revisar contratos, encargados, transferencias y garantías de los proveedores que realmente se utilizarán.
- [ ] Fijar conservación efectiva y mecanismo de eliminación. El texto actual prevé hasta 12 meses desde última comunicación para solicitudes sin relación contractual, salvo obligación legal; falta operativizar ese seguimiento.
- [ ] Completar nombres, duración y finalidad de las cookies reales tras probar la instalación.
- [ ] Revisar el consentimiento específico de identificación empresarial y las futuras etiquetas de Google.
- [ ] Eliminar frases provisionales de las políticas cuando se active cada servicio.
- [ ] Comprobar que la lectura/aceptación de privacidad no se presenta como alta en publicidad ni newsletter.
- [ ] Establecer un canal para derechos y reclamaciones y quién los atiende.
- [ ] Revisar permisos de publicación de clientes, materiales y personas del vídeo.

## 17. Publicación y pruebas finales

**Responsable: Desarrollo/hosting.**

### Preparar el despliegue

- [ ] Confirmar qué versión está realmente en producción; no sobrescribir cambios ajenos realizados desde la copia local.
- [ ] Hacer copia de seguridad de la web y configuración actual y preparar una forma de volver atrás.
- [ ] Confirmar PHP, sesiones, HTTPS, extensiones, conexiones salientes y variables del servidor.
- [ ] Regenerar recursos con `python3 tools/build-assets.py` si se modifican los archivos fuente.
- [ ] Publicar juntos `campanas/`, `api/lead.php`, los archivos `.htaccess` de raíz/campanas/api, los nuevos `includes/campaign-*.php`, `includes/tracking-config.php`, cabecera/pie, manifiesto, CSS/JS generados y políticas actualizadas.
- [ ] Comprobar en el hosting que home, API y campañas llevan no-store y no se cachean formularios de sesión en el CDN. La home debe seguir indexable; solo campañas/API llevan noindex.
- [ ] Publicar también los cambios de portada, páginas de email/contenidos/SEO-SEM y sitemap si aún no se habían subido.
- [ ] Subir vídeos y subtítulos aprobados cuando estén disponibles.
- [ ] Mantener la base de leads, backups, credenciales y exportaciones fuera de los archivos públicos.
- [ ] Revisar reglas de Apache/hosting y compatibilidad de `.htaccess`; evitar listados de carpetas.
- [ ] Purga de caché si procede, preservando las versiones de recursos necesarias para páginas ya abiertas.

### Prueba completa controlada antes de anuncios

- [ ] Abrir las once landings sin errores y comprobar CSS, imágenes y enlaces.
- [ ] Revisar móvil y escritorio en navegadores reales.
- [ ] Confirmar que las landings no aparecen en el menú público ni sitemap y tienen `noindex`.
- [ ] Completar el formulario con datos de prueba propios, permiso de privacidad y Turnstile válido.
- [ ] Confirmar un único lead guardado con el servicio y origen correctos.
- [ ] Confirmar llegada a la página de gracias y mensaje correcto.
- [ ] Confirmar el aviso al equipo y el agradecimiento al solicitante.
- [ ] Responder al email para comprobar las direcciones de respuesta.
- [ ] Recargar gracias y repetir clic para comprobar que no se duplica la solicitud ni la conversión.
- [ ] Probar campos incorrectos, sesión caducada, ausencia de aceptación y antispam inválido.
- [ ] Simular caída de correo: el lead debe quedar guardado y pendiente de envío.
- [ ] Probar fallo de almacenamiento: no debe mostrarse un éxito falso.
- [ ] Comprobar acceso privado a los datos, exportación y recuperación de pendientes.
- [ ] Verificar medición y consentimiento y después la identificación empresarial, si se activa.
- [ ] Revisar la web pública original para detectar regresiones.
- [ ] Ejecutar `php tools/tests/campaign-leads.php` después de cambios relevantes en el formulario.
- [ ] Registrar resultado de las pruebas y quién autoriza el lanzamiento de inversión.

## 18. Seguimiento tras el lanzamiento

**Responsables: Soto Digital y gestor de campañas.**

- [ ] Revisar a diario al inicio que se reciben solicitudes y se responden.
- [ ] Vigilar errores de servidor, almacenamiento, antispam y entrega de correo.
- [ ] Revisar gasto, términos de búsqueda y consultas irrelevantes; ajustar negativas y segmentación.
- [ ] Comparar leads recibidos con conversiones registradas para detectar pérdidas o duplicados.
- [ ] Registrar calidad del lead y evolución comercial, no solo formularios enviados.
- [ ] Evaluar por servicio inversión, consultas válidas, propuestas y clientes conseguidos.
- [ ] Revisar cobertura y utilidad de la identificación empresarial frente a su coste.
- [ ] Ajustar textos, anuncios y recorrido con evidencia suficiente.
- [ ] Actualizar casos de éxito cuando existan resultados verificables y permiso de publicación.
- [ ] Revisar backups, conservación y acceso a cuentas de forma periódica.

## 19. Material que debes reunir para poder terminar

- [ ] Responsable de las solicitudes y acceso al buzón de contacto.
- [ ] Acceso delegado a hosting y DNS.
- [ ] Decisión sobre presupuesto y servicios que se anunciarán primero.
- [ ] Cuenta de Google Ads y acceso al Planificador.
- [ ] Widget y claves de Turnstile.
- [ ] Cuenta Brevo, lista Leads web, atributos, dominio/remitente verificado y clave.
- [ ] Cuenta Anthropic API, clave y límites de uso.
- [ ] IDs y labels reales de Google Ads/GA4.
- [ ] Configuración de cron y del almacenamiento privado.
- [ ] Cuenta y código público de Leadfeeder si se quiere activar identificación empresarial.
- [ ] Vídeo de entrevista de Google Ads, después los del resto de servicios elegidos.
- [ ] MP4 optimizados y subtítulos VTT revisados.
- [ ] Fichas reales de los casos existentes, evidencias y permisos para destacados.
- [ ] Confirmación de la auditoría, propuesta y plazo de contacto que se ofrecerán.
- [ ] Aprobación de reglas de puntuación y alcance/entrega de la mini-auditoría gratuita.
- [ ] Decisión sobre ampliaciones: pipeline comercial, panel propio o IA para empresas visitantes.

## 20. Cuándo se considerará terminado

- [ ] Los nuevos servicios y todas las landings aprobadas están publicados y revisados.
- [ ] Google Ads tiene su landing específica y el vídeo real del equipo.
- [ ] Las páginas explican multicanalidad, auditoría, estrategia y proyección con claridad.
- [ ] Los casos describen resultados reales y los clientes destacados cumplen el criterio acordado.
- [ ] Una solicitud real se guarda, se confirma en pantalla y genera los dos emails.
- [ ] Las campañas usan palabras clave investigadas y destinos adecuados, con presupuesto aprobado.
- [ ] Las conversiones se registran una sola vez y respetan el consentimiento configurado.
- [ ] La identificación empresarial está activa y validada, si se ha decidido utilizarla.
- [ ] Brevo y Claude están configurados y probados con una solicitud real controlada; el worker funciona sin intervención manual en los casos normales.
- [ ] Hay responsables y procedimientos de respuesta, seguimiento, backups y eliminación.

Documentación técnica de apoyo: `docs/integracion-leads.md`, `docs/landings-google-ads.md`, `docs/identificacion-empresarial.md` y `docs/soto-config.example.php`.

## 21. Verificación de esta actualización (21/11/2026)

- [x] Sintaxis PHP de archivos modificados y compartidos comprobada.
- [x] Pruebas backend: campos y opciones, URL opcional, límites de puntuación, historial de reglas, filtro de spam, migración de base, cinco intentos/hora persistentes, creación/actualización CRM, minimización de datos IA y fallo de proveedores.
- [x] Pruebas de lógica JS: cinco pasos, validación, retroceso, errores de API y redirección; separación de consentimientos, migración de preferencias, almacenamiento bloqueado, retirada, deduplicación y WhatsApp.
- [x] HTTP local: once landings con recursos disponibles; home con cuestionario y ambos correos; home indexable y campañas noindex; endpoint devuelve errores JSON sin falso éxito cuando no hay configuración o la sesión no es válida.
- [ ] Revisión visual con navegador real (no había navegador conectado en el entorno).
- [ ] Envío completo real con Turnstile válido y credenciales, comprobando SQLite, worker, Brevo, acuse y aviso.
- [ ] Recepción real de eventos en Google y publicación.

Las pruebas usan datos de ejemplo y transportes simulados; no han enviado contactos o emails a servicios externos.

## 22. Enfoque geográfico de la comunicación

- [x] Ampliar titulares, títulos SEO y descripciones de los servicios digitales a empresas y negocios, sin limitarlos a Cantabria.
- [x] Actualizar la presentación de redes sociales con trabajo a distancia, planificación y contacto directo.
- [x] Aplicar el enfoque a la home y a los enlaces de servicios desde proyectos.
- [x] Mantener Cantabria como sede y la ubicación real de la pantalla LED en Raos.
- [ ] Publicar estos cambios junto al resto de la actualización y revisar los titulares en móvil.
- [ ] Elegir la segmentación geográfica de cada campaña según servicio y presupuesto; ampliar el texto de la web no modifica automáticamente campañas ni palabras clave.

## 23. Configuración privada local de Brevo preparada

- [x] Preparar configuración local fuera del directorio público y con permisos restringidos; configurar la ruta privada propia de Arsys antes de publicar.
- [x] Preparar la clave de Brevo en configuración privada. El cargador actual rechaza valores sin el formato real del proveedor; falta verificar una clave real en el hosting.
- [x] Conectar la configuración en `.htaccess` solo para localhost/127.0.0.1. En producción hay que definir SOTO_CONFIG_FILE con su propia ruta privada.
- [ ] Sustituir el marcador por una **clave API de Brevo**, no por la contraseña de acceso al panel. Editar solo el archivo privado, no `docs/soto-config.example.php`.
- [ ] Completar BREVO_LIST_ID y confirmar los atributos y el remitente ads@soto-digital.com en Brevo.
- [ ] Completar almacenamiento, Turnstile y cron según las secciones anteriores antes de activar el formulario y probar el circuito completo.

No se ha accedido a Brevo ni verificado una clave real. Para CLI/cron hay que indicar SOTO_CONFIG_FILE: la regla de localhost solo afecta a Apache.

## 24. WhatsApp flotante de las landings — histórico cancelado

Se implementó y probó en una fase anterior. Después se retiró por instrucción del usuario para centralizar la captación en el formulario. **No queda pendiente publicarlo, medirlo ni reactivarlo.** Véase el criterio vigente del apartado 25.

## 25. Contacto centralizado en formulario (criterio vigente)

- [x] Retirar el bloque Contacto completo del pie de página y adaptar el diseño a dos columnas.
- [x] Simplificar «Hagamos que pase algo» a texto y botón «Prefiero pedir una propuesta», dirigido al cuestionario.
- [x] Redirigir accesos de WhatsApp de home, servicios, proyectos y tarjetas digitales al formulario; actualizar textos e iconos para no prometer un chat.
- [x] Retirar teléfonos visibles y el teléfono de los datos estructurados.
- [x] Retirar el WhatsApp flotante de las landings y su carga de JavaScript.
- [x] Mantener el móvil del visitante en el formulario: se refiere al dato del lead, no al teléfono público de la agencia.
- [ ] Activar y probar el formulario antes de publicar: ahora es el canal principal de captación y siguen pendientes las credenciales/configuraciones descritas arriba.
- [ ] Revisar el diseño en móvil y escritorio y publicar la actualización con el manifiesto y CSS regenerados.

## 26. Datos legales confirmados y textos actualizados

- [x] Sociedad constituida: Soto Digital S.L.; NIF B05897236. Confirmado por el usuario.
- [x] Domicilio: Calle Galera 41, Polígono de Raos, 39600 Maliaño, Cantabria.
- [x] Privacidad: rebeca@soto-digital.com. Hosting confirmado: Arsys.
- [x] Decisión de utilizar Brevo, Claude API, Turnstile, Google Analytics, Google Ads y Leadfeeder/Dealfront. Esta decisión no acredita cuentas activas ni contratos revisados.
- [x] Conservación de solicitudes sin contratación: 12 meses desde la última comunicación, salvo obligaciones/responsabilidades aplicables.
- [x] Acceso interno limitado al personal autorizado de Soto Digital; no se prevé acceso comercial de freelancers. Los proveedores tratan lo necesario conforme a sus contratos.
- [x] Actualizar aviso legal, privacidad, cookies y aviso breve del formulario con estos datos.
- [x] Mantener sin suscripción automática a newsletters ni decisiones comerciales exclusivamente automatizadas; comportamiento ya implementado.
- [ ] Facilitar datos del Registro Mercantil para completar el aviso legal (provincia, hoja y referencia de inscripción/tomo/folio o identificación registral disponible).
- [ ] Revisar y documentar contratos de tratamiento, entidades prestadoras, regiones, subencargados y garantías de transferencias de las cuentas realmente contratadas. No se han aceptado contratos ni revisado cuentas privadas.
- [ ] Completar inventario de cookies y duraciones reales tras configurar las herramientas y antes de activarlas.
- [ ] Acordar caducidad/revisión de la elección de cookies y comprobarla técnicamente; el almacenamiento actual persiste hasta borrado/cambio/nueva versión.
- [ ] Implantar el registro de última comunicación y la eliminación efectiva a los 12 meses, también en Brevo y copias según su ciclo. El texto no sustituye el procedimiento.
- [ ] Documentar la base y ponderación del interés legítimo de seguridad/contacto profesional cuando se utilice y revisar los textos finales si cambian los usos.
- [ ] Revisión final por la persona responsable/asesoría y publicación. La actualización no se presenta como certificación de cumplimiento ni como política definitiva sin los datos y comprobaciones pendientes.

### Respuestas adicionales sobre privacidad

- [x] Revisión humana confirmada: Soto Digital revisará las recomendaciones de IA y la puntuación no descartará automáticamente clientes (respuesta 11).
- [x] El usuario indica que no dispone de gestoría/asesor de protección de datos para esta revisión (respuesta 12). No se presume revisión profesional de los textos.
- [ ] Aclarar la respuesta «no» al punto 10, formulado como confirmación de que no se añaden contactos automáticamente a newsletters. Hasta aclararlo se mantiene el comportamiento existente: ninguna suscripción automática y consentimiento separado para publicidad cuando corresponda.
