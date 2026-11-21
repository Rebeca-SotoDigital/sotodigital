# Integración real de Claude en local — 21/11/2026

Se mantiene el criterio del usuario: trabajo local, sin despliegue en Arsys.

## Comprobaciones realizadas

- Clave guardada exclusivamente en configuración privada. Consulta GET del modelo: 200, claude-haiku-5-5 accesible.
- Primera prueba sintética: HTTP 200 pero stop_reason=max_tokens; 350 tokens insuficientes porque el modelo consume parte en thinking. La respuesta incompleta no produjo un resumen guardado.
- Se ajusta a un máximo de 1200 tokens y effort=low para Haiku 5.5. La selección de otros modelos configurables no recibe ese parámetro automáticamente.
- Nueva prueba sintética: HTTP 200, end_turn, 253 tokens de entrada y 257 de salida. Resumen y siguiente paso válidos, estado complete y puntuación PHP intacta.
- El transporte de prueba comprobó una lista cerrada de nueve campos: sector, objective, budget, urgency, has_website, score, category, score_version y service. Las marcas ficticias de contacto, empresa, URL, mensaje e identificador de clic no se enviaron.
- Dos solicitudes internas guardadas: estado IA complete; resumen y siguiente paso locales coinciden con IA_NECESIDADES y SIGUIENTE_PASO_IA en Brevo, ESTADO_IA=complete, con puntuaciones PHP conservadas (5/10 en ambas). El worker sigue evitando reenviar emails ya aceptados.
- Tests automatizados: no aceptar respuestas truncadas, seleccionar únicamente bloques text y no permitir que un score propuesto en JSON cambie la puntuación PHP. Suites PHP/JS y sintaxis correctas.

## Límites y siguientes pasos

- Tres intentos automáticos máximos por solicitud; captura, Brevo y emails siguen siendo independientes de la disponibilidad del modelo.
- Resumen máximo de 600 caracteres y siguiente paso de 300, para revisión humana; no se ejecutan acciones propuestas por el modelo.
- El límite por petición no es un límite mensual ni una garantía de coste. Falta confirmar la configuración de gasto en la cuenta Anthropic.
- Revisión humana de calidad y prueba en el servidor definitivo pendientes. No se han configurado permisos de administrador de Anthropic ni un límite de gasto mediante esta API key.
- El paquete local se regenera con el cambio de código; no se publica.

Referencia técnica consultada: [migración Haiku 5.5](https://platform.claude.com/docs/en/models/haiku-5-5/migration-guide). La respuesta de la API real se usó para validar compatibilidad y acceso; no se incluyen claves ni payloads identificativos en este informe.
