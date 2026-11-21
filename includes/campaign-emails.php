<?php
function campaign_email_html(string $title, string $intro, string $body, string $reply): string {
    $e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
    return '<!doctype html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.$e($title).'</title></head><body style="margin:0;background:#f1f4f8;color:#142b53;font-family:Arial,sans-serif"><table role="presentation" style="width:100%;border-collapse:collapse"><tr><td style="padding:24px 12px"><table role="presentation" style="width:100%;max-width:600px;margin:auto;background:#fff;border-collapse:collapse"><tr><td style="padding:24px;background:#ffe44d;color:#142b53;font-size:20px;font-weight:bold">SOTO DIGITAL</td></tr><tr><td style="padding:28px;font-size:16px;line-height:1.65"><h1 style="margin:0 0 18px;font-size:26px;line-height:1.3">'.$e($title).'</h1><p>'.$e($intro).'</p>'.$body.'</td></tr><tr><td style="padding:22px 28px;background:#142b53;color:#fff;font-size:13px;line-height:1.7">Soto Digital S.L. · NIF B05897236<br>Puedes responder a este correo o escribir a <a style="color:#ffe44d" href="mailto:'.$e($reply).'">'.$e($reply).'</a>.</td></tr></table></td></tr></table></body></html>';
}
function campaign_email_messages(array $row, array $data, array $score, string $admin): array {
    $e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
    $options=campaign_options();$services=require __DIR__.'/campaign-services.php';$service=$services[$row['service']][0]??$row['service'];
    $details=['Servicio'=>$service,'Nombre'=>$data['name'],'Empresa'=>($data['company']??'')?:'No indicada','Email'=>$data['email'],'Móvil'=>$data['phone']?:'No indicado','Web'=>$data['website']?:'No indicada'];
    foreach(['sector','objective','budget','urgency'] as $key)$details[$options[$key]['label']]=$options[$key]['options'][$data[$key]??'']??'No indicado';
    $details['Atención']='Jónatan y Rebeca';
    $details['Primera respuesta']='Máximo '.campaign_response_time();
    $details['Puntuación']=$score['score'].'/10 · '.$score['category'].' ('.$score['score_version'].')';
    $details['Resumen IA (revisar)']=$row['ai_summary']?:'No disponible';$details['Siguiente paso IA (revisar)']=$row['ai_next_step']?:'Revisar y contactar';
    $text='';$table='<table role="presentation" style="width:100%;border-collapse:collapse">';
    foreach($details as $label=>$value){$text.=$label.': '.$value."\n";$table.='<tr><td style="padding:10px 0;border-bottom:1px solid #dbe1ea"><strong>'.$e($label).'</strong><br>'.$e($value).'</td></tr>';}
    $table.='</table><h2 style="font-size:18px">Contexto adicional</h2><p>'.nl2br($e($data['message']?:'No indicado')).'</p>';
    $response=campaign_response_time();
    $next='Revisaremos los datos que nos has enviado. Según las necesidades de tu negocio, '.($data['phone']!==''?'te llamaremos o te enviaremos una propuesta por email.':'te contactaremos por email con una propuesta.');
    $timing='Primera respuesta en un máximo de '.$response.'.';
    $confirmation='Hola '.$data['name'].",\n\nHemos recibido tu solicitud sobre ".$service.".\n\n".$next."\n".($timing!==''?$timing."\n":'')."\nSi quieres añadir información, responde a este correo.\n\nSin compromiso de contratación. Este mensaje confirma tu solicitud; no te hemos suscrito a newsletters.\n\nEl equipo de Soto Digital\n".$admin;
    $body='<p>Servicio: <strong>'.$e($service).'</strong></p><ol style="padding-left:22px"><li>Revisamos los datos que nos has facilitado.</li><li>Valoramos las necesidades de tu negocio.</li><li>Según el caso, te llamamos si has dejado móvil o te enviamos una propuesta por email.</li></ol>'.($timing!==''?'<p>'.$e($timing).'</p>':'').'<p>Si quieres añadir información, responde a este correo.</p><p style="font-size:14px">Sin compromiso de contratación. Este mensaje confirma tu solicitud; no te hemos suscrito a newsletters.</p>';
    return [
      'admin_sent'=>['to'=>[$admin],'reply_to'=>$data['email'],'subject'=>'Nueva mini-auditoría | Soto Digital','text'=>$text."\n".$data['message'],'html'=>campaign_email_html('Nueva solicitud de mini-auditoría','Jónatan y Rebeca: revisad los datos y coordinad la primera respuesta para evitar duplicados.',$table,$admin)],
      'confirmation_sent'=>['to'=>[$data['email']],'reply_to'=>$admin,'subject'=>'Hemos recibido tu solicitud | Soto Digital','text'=>$confirmation,'html'=>campaign_email_html('Hemos recibido tu solicitud','Hola '.$data['name'].', gracias por contarnos tu proyecto.',$body,$admin)],
    ];
}
