# Prueba real de Brevo — 21/11/2026

Prueba autorizada por el usuario: alta de contacto y dos emails reales al buzón propio ads@soto-digital.com. Ejecutada aproximadamente a las 17:05, Europe/Madrid.

- Identificador: `SOTO-734c0fdc`.
- Contacto Brevo ID 101: PRUEBA TECNICA SOTO DIGITAL, ads@soto-digital.com. No existía previamente (GET 404); no se sobrescribió un contacto real.
- Creado en la lista configurada y verificado por GET: pertenencia correcta, PUNTUACION=9 y POTENCIAL=caliente. 3 presupuesto + 2 objetivo + 3 urgencia + 1 web + 0 teléfono.
- El cálculo usa PHP, con el código real de la integración. No se llamó a Claude ni se inventó un resumen de IA.
- Aviso al equipo y confirmación con el código real de envío, ambos dirigidos a ads@soto-digital.com, con prefijo [PRUEBA REAL SOTO-734c0fdc] y aviso explícito de prueba técnica.
- Ambas llamadas fueron aceptadas y los registros transaccionales de Brevo confirman dos eventos delivered a las 17:05:28 +01:00. Esto acredita aceptación por el servidor destinatario, no lectura ni ubicación en bandeja principal.
- Contacto conservado para que el equipo revise la prueba; no se borró ni se suscribió a newsletters desde el código. La prueba no evaluó automatizaciones preexistentes de la cuenta.
- Almacenamiento de prueba SQLite aislado en carpeta temporal privada. No se ejecutó la cola de producción ni se generó una conversión Google.

## Alcance pendiente

Esta prueba valida directamente el tramo PHP → contacto Brevo → emails reales y entrega. No prueba navegación/envío del formulario, Turnstile, almacenamiento definitivo, cron del hosting, IA real ni medición. Falta esa prueba completa en el entorno de publicación.
