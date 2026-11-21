# Identificación empresarial — Leadfeeder / Dealfront

Estado: preparada en local, desactivada por defecto. Sin cuenta ni envío de datos del rastreador. La identificación empresarial no tiene almacenamiento propio ni automatización de IA; el formulario de solicitudes sí dispone de guardado privado y un flujo separado con Brevo/Claude. Las empresas se consultarán y guardarán en el panel del proveedor; exportación y CRM dependen del plan contratado.

## Activación

1. Crear o abrir una cuenta de Leadfeeder para soto-digital.com.
2. Settings > Company > Website Tracker > Tracker script: copiar el código oficial. Extraer la URL completa de `https://sc.lftracker.com/…js`. No es una clave API ni una contraseña.
3. Configurar en PHP/Apache la variable `LEADFEEDER_TRACKER_URL` con esa URL pública. No pegar el snippet por separado: duplicaría el seguimiento y eludiría el consentimiento de esta integración. Si el dominio del snippet difiere, adaptar la validación tras verificar el código oficial.
4. Mantener el modo de consentimiento de cookies del proveedor desactivado para esta instalación: el sitio bloquea el script completo hasta el permiso específico. Si se activa ese modo, integrar además `window.ldfdr.acceptCookie()` según la documentación antes de lanzar.
5. Confirmar las reglas de hostname para soto-digital.com y www si se utiliza. Excluir tráfico interno y de pruebas. Deshabilitar cualquier función de captura de formularios o identificación personal que no se necesite.
6. Revisar el contrato de tratamiento, subencargados, transferencias y retención elegidos. Completar las políticas locales con las duraciones reales de cookies y conservación y los datos contractuales del proveedor antes de publicar la activación. Los textos actuales explican el estado de preparación.
7. Publicar los PHP modificados, `js/cookies.js`, su versión generada y `includes/asset-manifest.php` juntos.

## Verificación en producción

- Sin decidir, al rechazar y con consentimientos anteriores a versión 3: ninguna petición a lftracker.
- Aceptar solo analíticas/publicidad: ninguna petición a lftracker.
- Aceptar identificación empresarial: un solo script, con la URL de la cuenta correcta.
- Retirar el permiso: cookies conocidas eliminadas, recarga y ausencia de nuevas peticiones al proveedor.
- El bloqueo de almacenamiento o de scripts no debe impedir navegar. Los bloqueadores pueden impedir identificar empresas.
- Verificar recepción en Website Tracker y nuevas empresas en el panel (puede tardar hasta una hora). No garantiza reconocer todas las empresas ni personas.
- El historial ya enviado no se borra al retirar cookies: gestionar solicitudes y retención en el proveedor.

## Fuentes oficiales

- https://help.leadfeeder.com/en/articles/13921717-installing-the-leadfeeder-tracking-script-directly-on-your-website
- https://help.leadfeeder.com/en/articles/13922292-configuring-the-leadfeeder-tracking-script-with-your-consent-management-platform-cmp

Para desactivar: quitar `LEADFEEDER_TRACKER_URL` del servidor. No añadir claves API al navegador.
