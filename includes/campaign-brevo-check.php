<?php
/** Read-only preflight: no contacts, messages or database writes. Never returns provider bodies. */
function campaign_brevo_check(?callable $http=null): array {
    $checks=[];
    $record=static function($name,$ok,$detail)use(&$checks){$checks[$name]=['ok'=>(bool)$ok,'detail'=>$detail];};
    $key=campaign_config('BREVO_API_KEY');$list=(int)campaign_config('BREVO_LIST_ID');
    $from=campaign_config('CAMPAIGN_MAIL_FROM');$to=campaign_config('CAMPAIGN_MAIL_TO','ads@soto-digital.com');
    $record('api_key',(bool)$key,$key?'Clave configurada; se validará contra Brevo.':'Introduce una clave API real en BREVO_API_KEY, en configuración privada.');
    $record('list_configured',$list>0,$list>0?'ID de lista configurado.':'Completa BREVO_LIST_ID con el ID numérico de Leads web.');
    $record('mail_configured',filter_var($from,FILTER_VALIDATE_EMAIL)&&filter_var($to,FILTER_VALIDATE_EMAIL),'CAMPAIGN_MAIL_FROM y CAMPAIGN_MAIL_TO deben ser emails válidos.');
    if(!$key)return ['ok'=>false,'checks'=>$checks];
    $http=$http??'campaign_http';$headers=['api-key: '.$key];
    $get=static function($path)use($http,$headers){return $http('https://api.brevo.com/v3'.$path,$headers,[],12,'GET');};
    $account=$get('/account');$authenticated=$account['status']===200;
    $record('account',$authenticated,$authenticated?'Brevo acepta la clave.':'No se pudo validar la cuenta (HTTP '.(int)$account['status'].'); revisar clave, permisos, IP autorizada o disponibilidad.');
    if(!$authenticated)return ['ok'=>false,'checks'=>$checks];
    if($list>0){
        $response=$get('/contacts/lists/'.$list);
        $ok=$response['status']===200 && (int)($response['body']['id']??0)===$list && empty($response['body']['dynamicList']);
        $record('list',$ok,$ok?'La lista configurada existe y admite contactos.':'Lista no disponible o dinámica; revisar BREVO_LIST_ID y permisos.');
    }
    $schema=campaign_brevo_schema($http,$headers);
    if($schema===null)$record('attributes',false,'No se pudieron consultar los atributos.');
    else {
        try {
            campaign_brevo_validate_schema($schema);
            // Check all required values, not just whichever category one test lead uses.
            $row=['service'=>'google-ads','created_at'=>'2026-11-21T00:00:00Z','id'=>'preflight','consent_version'=>'preflight','ai_summary'=>'Resumen','ai_next_step'=>'Revisar','ai_status'=>'complete'];
            $data=['name'=>'Prueba local','company'=>'Empresa','website'=>'https://example.com','message'=>'Contexto','phone'=>'','budget'=>'1000-3000','objective'=>'consultas','urgency'=>'este-mes'];
            foreach(['google-ads','seo','redes-sociales','diseno-web','branding','email-marketing','estrategia-digital'] as $service){$row['service']=$service;campaign_brevo_attributes(campaign_brevo_values($row,$data,['landing'=>'/','utm_campaign'=>'preflight']),$schema);}
            campaign_brevo_attributes(['ORIGEN'=>'Google Ads','ESTADO_IA'=>'pending'],$schema);
            foreach(['caliente','templado','frio'] as $category)campaign_brevo_attributes(['POTENCIAL'=>$category],$schema);
            $record('attributes',true,'Atributos, tipos y categorías compatibles.');
        }catch(RuntimeException $e){
            $detail=preg_match('/^Brevo (?:attribute missing|attribute type needs configuration|category needs configuration): [A-Z_]+$/D',$e->getMessage())?$e->getMessage():'Revisa los tipos y opciones de los atributos.';
            $record('attributes',false,$detail);
        }
        $missing=array_values(array_diff(['GCLID','GBRAID','WBRAID','UTM_SOURCE','UTM_MEDIUM','SERVICIO','ID_SOLICITUD','VERSION_PRIVACIDAD'],array_keys($schema)));
        $checks['optional_attributes']=['ok'=>true,'detail'=>$missing?'Opcionales ausentes (se conservan localmente): '.implode(', ',$missing):'Atribución y trazabilidad opcionales disponibles.'];
    }
    if(filter_var($from,FILTER_VALIDATE_EMAIL)){
        $response=$get('/senders');$active=false;
        if($response['status']===200)foreach($response['body']['senders']??[] as $sender)if(strcasecmp($sender['email']??'',$from)===0&&!empty($sender['active']))$active=true;
        $record('sender',$active,$active?'Remitente configurado activo en Brevo.':'No se pudo verificar un remitente activo igual a CAMPAIGN_MAIL_FROM.');
        $domain=substr(strrchr($from,'@'),1);$response=$get('/senders/domains/'.rawurlencode($domain));
        $authenticated=$response['status']===200 && ($response['body']['authenticated']??false)===true;
        $record('domain',$authenticated,$authenticated?'Dominio autenticado en Brevo.':'Autenticación del dominio pendiente o no verificable; revisar DNS en Brevo.');
    }
    return ['ok'=>!in_array(false,array_column($checks,'ok'),true),'checks'=>$checks];
}
