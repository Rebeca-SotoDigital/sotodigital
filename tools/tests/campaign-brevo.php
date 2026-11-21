<?php
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/../../includes/campaign-leads.php';
require __DIR__.'/brevo-schema-fixture.php';
function brevo_assert($ok,$message){if(!$ok)throw new RuntimeException($message);}
putenv('BREVO_API_KEY=xkeysib-local-fixture');putenv('BREVO_LIST_ID=123');
$db=new PDO('sqlite::memory:');$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);campaign_migrate($db);
$input=['name'=>'Nombre compuesto Apellidos','email'=>'test@example.com','company'=>'Empresa privada de prueba','phone'=>'600 000 000','website'=>'https://example.com','message'=>'Texto privado','privacy'=>'1','sector'=>'servicios','objective'=>'consultas','budget'=>'1000-3000','urgency'=>'este-mes','score'=>0,'category'=>'frio'];
$data=campaign_validate($input);campaign_store($db,'new','redes-sociales',$data,['utm_source'=>'google','utm_medium'=>'organic','landing'=>'/campanas/redes-sociales.php']);
$row=$db->query("SELECT * FROM leads WHERE id='new'")->fetch(PDO::FETCH_ASSOC);
$values=campaign_brevo_values($row,json_decode($row['payload'],true),json_decode($row['attribution'],true));
brevo_assert($values['PUNTUACION']===10 && $values['POTENCIAL']==='caliente','Client-injected score accepted');
brevo_assert($values['ORIGEN']==='Web','Organic Google classified as ads');
brevo_assert(campaign_brevo_values($row,$data,['wbraid'=>'test'])['ORIGEN']==='Google Ads','Braid click missed');
$schema=campaign_brevo_test_schema();
foreach($schema as &$field){
 if($field['name']==='SERVICIO_INTERES'){$field['category']='category';$field['type']='text';$field['enumeration']=[['label'=>'Redes','value'=>0,'valueStr'=>'social']];}
 if($field['name']==='ESTADO_LEAD'){$field['category']='category';$field['type']='text';$field['enumeration']=[['label'=>'Nuevo','value'=>8,'valueStr'=>'8']];}
}unset($field);
$posts=[];$http=static function($url,$headers,$payload,$timeout=12,$method='POST')use($schema,&$posts){
 if(str_ends_with($url,'/attributes'))return ['status'=>200,'body'=>['attributes'=>$schema]];
 if($method==='GET')return ['status'=>404,'body'=>[]];
 $posts[]=$payload;
 return count($posts)===1?['status'=>400,'body'=>['code'=>'duplicate_parameter']]:['status'=>201,'body'=>['id'=>321]];
};
campaign_brevo_sync($db,$row,$http);
brevo_assert(count($posts)===2 && $posts[0]['attributes']['SMS']==='+34600000000' && !isset($posts[1]['attributes']['SMS']),'Phone normalization/collision fallback');
$attrs=$posts[1]['attributes'];
brevo_assert($attrs['SERVICIO_INTERES']==='social'&&$attrs['ESTADO_LEAD']==='8','Category IDs/valueStr not resolved');
brevo_assert($attrs['EMPRESA']===$input['company']&&$attrs['RGPD_ACEPTADO']===true&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$attrs['FECHA_RGPD']),'Capture mapping/consent');
foreach(['IA_PUNTUACION','IA_CATEGORIA','IMPORTE_SETUP','CONTRATO_FIRMADO'] as $forbidden)brevo_assert(!array_key_exists($forbidden,$attrs),'Unrequested field: '.$forbidden);
brevo_assert($db->query("SELECT brevo_synced FROM leads WHERE id='new'")->fetchColumn()==1,'Contact not marked synced');
// Re-enrichment must preserve a real existing business stage and optional contact data.
$db->exec("UPDATE leads SET brevo_synced=0 WHERE id='new'");$row=$db->query("SELECT * FROM leads WHERE id='new'")->fetch(PDO::FETCH_ASSOC);
$payload=json_decode($row['payload'],true);$payload['company']='';$payload['phone']='';$payload['website']='';$row['payload']=json_encode($payload);
$existingPost=[];$existing=static function($url,$headers,$payload,$timeout=12,$method='POST')use($schema,&$existingPost){
 if(str_ends_with($url,'/attributes'))return ['status'=>200,'body'=>['attributes'=>$schema]];
 if($method==='GET')return ['status'=>200,'body'=>['attributes'=>['ESTADO_LEAD'=>'Contrato firmado']]];
 $existingPost=$payload;return ['status'=>204,'body'=>[]];
};
campaign_brevo_sync($db,$row,$existing);
foreach(['ESTADO_LEAD','EMPRESA','WEB_CLIENTE','SMS'] as $name)brevo_assert(!array_key_exists($name,$existingPost['attributes']),'Existing contact data reset: '.$name);
$missing=static function($url,$headers,$payload,$timeout=12,$method='POST')use($schema){return ['status'=>200,'body'=>['attributes'=>array_values(array_filter($schema,fn($v)=>$v['name']!=='PUNTUACION'))]];};
$db->exec("UPDATE leads SET brevo_synced=0 WHERE id='new'");$row=$db->query("SELECT * FROM leads WHERE id='new'")->fetch(PDO::FETCH_ASSOC);
try{campaign_brevo_sync($db,$row,$missing);throw new RuntimeException('Missing schema accepted');}catch(RuntimeException $e){brevo_assert(str_contains($e->getMessage(),'PUNTUACION'),'Wrong schema error');}
brevo_assert($db->query("SELECT brevo_synced FROM leads WHERE id='new'")->fetchColumn()==0,'Missing schema silently marked synchronized');
brevo_assert($db->query("SELECT COUNT(*) FROM leads")->fetchColumn()==1,'Schema failure lost lead');
brevo_assert(campaign_brevo_phone('0034600000000')==='+34600000000'&&campaign_brevo_phone('1234567')==='','Phone format');
echo "PASS: Brevo schema/category IDs, PHP-only score, attribution, phone retry, existing stage/empty preservation and recoverable schema failure\n";
