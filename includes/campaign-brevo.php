<?php
/** CRM vocabulary is independent of the questionnaire's internal keys. */
function campaign_brevo_values(array $row, array $data, array $attr): array {
    $services = ['chatgpt-ads'=>'ChatGPT Ads','google-ads'=>'Google Ads','seo'=>'SEO','redes-sociales'=>'Redes sociales','diseno-web'=>'Diseño web','branding'=>'Marca','email-marketing'=>'Email marketing'];
    $paid = !empty($attr['gclid']) || !empty($attr['gbraid']) || !empty($attr['wbraid']) ||
        (strtolower($attr['utm_source'] ?? '') === 'google' && in_array(strtolower($attr['utm_medium'] ?? ''), ['cpc','ppc','paid','paidsearch'], true));
    $score = campaign_stored_score($data);
    return [
        'FIRSTNAME'=>$data['name'], 'EMPRESA'=>$data['company'] ?? '', 'WEB_CLIENTE'=>$data['website'],
        'SERVICIO_INTERES'=>$services[$row['service']] ?? 'Otro', 'MENSAJE_LEAD'=>$data['message'],
        'ORIGEN'=>$paid ? 'Google Ads' : 'Web', 'LANDING'=>$attr['landing'] ?? '',
        'UTM_SOURCE'=>$attr['utm_source'] ?? '', 'UTM_MEDIUM'=>$attr['utm_medium'] ?? '', 'UTM_CAMPAIGN'=>$attr['utm_campaign'] ?? '',
        'UTM_CONTENT'=>$attr['utm_content'] ?? '', 'UTM_TERM'=>$attr['utm_term'] ?? '',
        'GCLID'=>$attr['gclid'] ?? '', 'GBRAID'=>$attr['gbraid'] ?? '', 'WBRAID'=>$attr['wbraid'] ?? '',
        'FECHA_ALTA_LEAD'=>substr($row['created_at'],0,10), 'RGPD_ACEPTADO'=>true,
        'FECHA_RGPD'=>substr($row['created_at'],0,10), 'ESTADO_LEAD'=>'Nuevo',
        'PUNTUACION'=>$score['score'], 'POTENCIAL'=>$score['category'], 'VERSION_PUNTUACION'=>$score['score_version'],
        'IA_NECESIDADES'=>$row['ai_summary'], 'RESUMEN_IA'=>$row['ai_summary'], 'SIGUIENTE_PASO_IA'=>$row['ai_next_step'], 'ESTADO_IA'=>$row['ai_status'],
        // Optional technical attributes: sent only when they exist in the account.
        'SERVICIO'=>$row['service'], 'SECTOR'=>$data['sector'] ?? '', 'OBJETIVO'=>$data['objective'] ?? '',
        'PRESUPUESTO'=>$data['budget'] ?? '', 'URGENCIA'=>$data['urgency'] ?? '',
        'ID_SOLICITUD'=>$row['id'], 'VERSION_PRIVACIDAD'=>$row['consent_version'],
    ];
}
function campaign_brevo_phone(string $raw): string {
    $phone = preg_replace('/[\s().-]/', '', $raw);
    if (str_starts_with($phone, '00')) $phone = '+'.substr($phone,2);
    if (preg_match('/^[6-9][0-9]{8}$/D',$phone)) $phone='+34'.$phone;
    return preg_match('/^\+[1-9][0-9]{7,14}$/D',$phone) ? $phone : '';
}
function campaign_brevo_schema(callable $http, array $headers): ?array {
    // Once per worker/transport, no persistent cache to mask account changes.
    static $schemas=[];
    $transport=is_object($http) ? spl_object_id($http) : (is_string($http)?$http:json_encode($http));
    $key=hash('sha256',campaign_config('BREVO_API_KEY').'|'.$transport);
    if(isset($schemas[$key])) return $schemas[$key];
    $result=$http('https://api.brevo.com/v3/contacts/attributes',$headers,[],12,'GET');
    if($result['status']!==200 || !is_array($result['body']['attributes']??null))return null;
    $schema=[];foreach($result['body']['attributes'] as $attribute) if(isset($attribute['name']))$schema[$attribute['name']]=$attribute;
    return $schemas[$key]=$schema;
}
function campaign_brevo_attributes(array $values, array $schema): array {
    $attributes=[];
    foreach($values as $name=>$value){
        if($value==='' || !isset($schema[$name]))continue; // Do not erase an existing contact with empty optional fields.
        $definition=$schema[$name];$type=$definition['type']??'';
        if ($type === 'float') $type = 'number';
        if($type==='category' || ($definition['category']??'')==='category'){
            $choices=[$value];
            if($name==='SERVICIO_INTERES' && $value==='Redes sociales')$choices[]='Redes';
            if($name==='POTENCIAL')$choices[]=ucfirst($value);
            $resolved=null;
            foreach($definition['enumeration']??[] as $option)if(in_array($option['label']??null,$choices,true)){$resolved=$option['valueStr']??$option['value'];break;}
            if($resolved===null)throw new RuntimeException('Brevo category needs configuration: '.$name);
            $value=$resolved;
        } elseif(($type==='text' && !is_string($value)) || ($type==='number' && !is_int($value) && !is_float($value)) || ($type==='boolean' && !is_bool($value)) || ($type==='date' && (!is_string($value)||!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$value))) || !in_array($type,['text','number','boolean','date'],true)){
            throw new RuntimeException('Brevo attribute type needs configuration: '.$name);
        }
        $attributes[$name]=$value;
    }
    return $attributes;
}

function campaign_brevo_validate_schema(array $schema): void {
    foreach (['FIRSTNAME','EMPRESA','WEB_CLIENTE','SERVICIO_INTERES','MENSAJE_LEAD','ORIGEN','LANDING','UTM_CAMPAIGN','FECHA_ALTA_LEAD','RGPD_ACEPTADO','FECHA_RGPD','ESTADO_LEAD','PUNTUACION','POTENCIAL','VERSION_PUNTUACION','IA_NECESIDADES','SIGUIENTE_PASO_IA','ESTADO_IA'] as $required) {
        if (!isset($schema[$required])) throw new RuntimeException('Brevo attribute missing: '.$required);
    }
    foreach (['PUNTUACION'=>'number','RGPD_ACEPTADO'=>'boolean','FECHA_RGPD'=>'date','FECHA_ALTA_LEAD'=>'date'] as $name=>$type) {
        if (!in_array($schema[$name]['type'] ?? '', $type === 'number' ? ['number','float'] : [$type], true) || ($schema[$name]['category'] ?? '') === 'category') throw new RuntimeException('Brevo attribute type needs configuration: '.$name);
    }
}
