<?php
function campaign_brevo_test_schema(): array {
    $types=[
        'FIRSTNAME'=>'text','EMPRESA'=>'text','WEB_CLIENTE'=>'text','SERVICIO_INTERES'=>'text','MENSAJE_LEAD'=>'text',
        'ORIGEN'=>'text','LANDING'=>'text','UTM_CAMPAIGN'=>'text','FECHA_ALTA_LEAD'=>'date','RGPD_ACEPTADO'=>'boolean',
        'FECHA_RGPD'=>'date','ESTADO_LEAD'=>'text','PUNTUACION'=>'float','POTENCIAL'=>'text','VERSION_PUNTUACION'=>'text',
        'IA_NECESIDADES'=>'text','SIGUIENTE_PASO_IA'=>'text','ESTADO_IA'=>'text',
        'SECTOR'=>'text','OBJETIVO'=>'text','PRESUPUESTO'=>'text','URGENCIA'=>'text','GCLID'=>'text','GBRAID'=>'text','WBRAID'=>'text','UTM_SOURCE'=>'text',
    ];
    $attributes=[];foreach($types as $name=>$type)$attributes[]=['name'=>$name,'type'=>$type,'category'=>'normal'];
    return $attributes;
}
