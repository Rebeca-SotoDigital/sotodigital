# Siguiente etapa: CRM y conversiones offline

Estado: diseño y esqueleto local, sin activar cuentas ni importar conversiones.

## Brevo

Crear segmentos con POTENCIAL igual a caliente, templado o frio. PUNTUACION es numérico y VERSION_PUNTUACION identifica la tabla; no reemplazar puntuaciones históricas. Crear pipeline **Nuevo → Cualificado → Propuesta enviada → Contrato firmado → Pagado → En ejecución** con un responsable del equipo. La clasificación orienta revisión humana, no sustituye la cualificación.

`BREVO_CREATE_DEALS=false` de forma predeterminada. Para una activación posterior hacen falta `BREVO_PIPELINE_ID`, `BREVO_NEW_STAGE_ID`, clave y contacto sincronizado. El worker prepara POST `/crm/deals` solo para calientes y enlaza el ID del contacto; guarda ID/estado del trato. No mueve etapas ni crea propuestas, contratos, pagos o encargos. Ante respuesta ambigua queda `sending`: revisar Brevo antes de rearmar, para no crear otro trato. Rechazos definitivos quedan pendientes; corregir cuenta/atributos antes de reintentar.

Referencia comprobada: [crear trato en Brevo](https://developers.brevo.com/reference/create-a-deal). Confirmar IDs de atributos/etapas en vuestra cuenta; no hay IDs ficticios activados.

## Diseño de exportación offline (sin implementación)

Objetivo: medir una adquisición real después de revisión humana, con una acción de conversión independiente del formulario. No se ha creado exportador, CSV, acción Ads ni llamada de importación.

1. Registrar manualmente cuándo un lead se convierte en cliente, valor económico justificado y moneda. Definir qué hito cuenta (contrato o pago) y el responsable que lo valida. Nunca inferir valor desde la puntuación ni desde el presupuesto declarado.
2. Comprobar permisos/base jurídica aplicables al uso publicitario del identificador; el consentimiento analítico no basta. El código actual guarda atribución asociada a la solicitud: esta fase debe revisar minimización y usos antes de exportar.
3. Seleccionar un identificador de clic compatible con el método de importación elegido: GCLID o los identificadores braid admitidos. Confirmar documentación vigente de Google para el destino: no asumir que todos comparten la misma columna/formato CSV.
4. Fila de diseño: ID interno privado, identificador/tipo de clic, nombre exacto de acción, fecha real de conversión con zona horaria explícita, valor, moneda, clave única de conversión y estado de envío. El fichero final usará exclusivamente las columnas y formato que exija Google; el ID interno sirve para trazabilidad privada.
5. Cero nombre, email, móvil, web, texto libre o resumen IA en el export. Tratar también el identificador de clic como dato protegido, con permisos, plazo de 12 meses y supresión. No generar un fichero público ni adjuntarlo a informes agregados.
6. Deduplicar por lead/hito/acción y registrar resultado de importación. Revisar ventana máxima admitida y rechazos en Google; no alterar fechas para forzar aceptación. Evitar sumar formulario y cliente como si fueran el mismo resultado principal.
7. Prueba manual con datos propios y una conversión autorizada antes de automatizar; verificar borrado, auditoría y rectificaciones. La importación real queda fuera de esta entrega.

## Informe semanal

`php bin/campaign-leads.php report --week` muestra la semana actual UTC (lunes inclusive a lunes siguiente exclusivo), por servicio, campaña, potencial y procesado/pendiente. Solo recuentos. Las campañas no numéricas se sustituyen por un hash para evitar textos potencialmente personales. No incluye contactos ni valores de venta. Guardar el informe en un destino privado; no se manda por email automáticamente.
