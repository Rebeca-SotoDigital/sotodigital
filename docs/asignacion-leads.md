# Asignación manual de solicitudes

Configuración verificada el 21 de noviembre de 2026. Jónatan y Rebeca comparten acceso a Brevo: se usa un atributo de contacto, no el propietario nativo asociado a usuarios distintos.

## Preparación en Brevo

1. Abrir un contacto y configurar los atributos visibles para mostrar `RESPONSABLE_LEAD`.
2. Añadir también esa columna a la lista de contactos para consultar rápidamente las asignaciones.
3. El atributo ya está creado y verificado: categoría con Jónatan (1) y Rebeca (2). Vacío significa que nadie se ha asignado todavía. El equipo confirma que ve el campo. Para la prueba posterior solo se modificó temporalmente el contacto interno 101; sus valores originales se restauraron y no se enviaron mensajes.

## Cada solicitud

1. Abrir la ficha desde el aviso interno o desde Contactos en Brevo.
2. Comprobar `RESPONSABLE_LEAD`. Si está vacío, elegir el propio nombre y guardar antes de contactar. Si tiene nombre, coordinarse con esa persona antes de responder.
3. Revisar los datos y la acción sugerida por IA; primera respuesta en un máximo de 2 días laborables. Según las necesidades, llamar si hay teléfono o enviar propuesta por email.
4. Registrar el resultado en el seguimiento comercial de Brevo. Utilizar los estados aprobados que se detallan a continuación. Ambos revisan las solicitudes pendientes; no hay recordatorios automáticos.

El acceso compartido no identifica quién abre una ficha ni garantiza que dos personas no la editen a la vez. Esta regla requiere coordinación; no hay reserva automática, avisos de vencimiento ni asignación al abrir el contacto. Si ambos entran a la vez, deben confirmar entre ellos quién lo atiende.

## Conservación y comprobación

La web no envía `RESPONSABLE_LEAD` ni `CONTACT_OWNER` en sus actualizaciones: conserva el responsable manual y el estado comercial existente. Las pruebas con transporte simulado verifican ese comportamiento y que el ID del contacto se conserva si Brevo responde 204. El enlace a Brevo solo aparece en el aviso interno, nunca en la confirmación al solicitante.

## Estados aprobados

- **Nuevo:** solicitud recibida, pendiente de revisión.
- **En revisión:** responsable asignado, revisando los datos y el siguiente paso.
- **Contactado:** se ha realizado el primer contacto; registrar en Brevo lo sucedido, incluida la falta de respuesta si procede.
- **Propuesta enviada:** propuesta efectivamente enviada, pendiente de decisión.
- **Ganado:** contratación confirmada por el cliente. No equivale automáticamente a contrato firmado o cobro recibido.
- **Perdido:** no se continúa con la oportunidad; dejar anotado el motivo.

Ganado y Perdido son salidas alternativas, no pasos consecutivos obligatorios. Los estados se cambian manualmente según hechos reales. Se conservan las opciones anteriores Calificado, Aceptada, Contrato firmado y Pagado con sus IDs; no se reclasifican contactos existentes. No se han creado tratos ni activado automatizaciones.

## Prueba real completada

El 21/11/2026 se verificaron los seis estados mediante la API real de Brevo en el contacto interno 101. Se alternaron Jónatan y Rebeca y se ejecutó la función de sincronización de la web después de cada cambio, usando una copia en SQLite en memoria de la solicitud interna. Las seis comprobaciones conservaron estado, responsable, ID, puntuación y textos de IA. Al terminar se restauraron el estado y responsable originales y se verificó la restauración.

No se enviaron emails ni peticiones a Claude, ni se modificaron filas de leads en la base local. Informe: [prueba-recorrido-comercial.json](prueba-recorrido-comercial.json). La prueba valida la API y la sincronización; la coordinación diaria entre ambos sigue siendo manual. No se ha publicado en Arsys.
