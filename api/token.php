<?php
require __DIR__.'/../includes/campaign-leads.php';
require_once __DIR__.'/../includes/site-paths.php';
header('Cache-Control: private, no-store');header('X-Robots-Tag: noindex, nofollow');header('Content-Type: application/json; charset=UTF-8');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);header('Allow: POST');exit('{"ok":false}');}
if(($_SERVER['HTTP_X_SOTO_FORM']??'')!=='1' || ($_SERVER['HTTP_SEC_FETCH_SITE']??'same-origin')==='cross-site'){http_response_code(403);exit('{"ok":false}');}
if(!campaign_ready()){http_response_code(503);exit('{"ok":false}');}
$input=json_decode(file_get_contents('php://input'),true);
if(!is_array($input)){http_response_code(400);exit('{"ok":false}');}
$services=require __DIR__.'/../includes/campaign-services.php';
$service=$input['service']??'';if(!is_string($service)||!isset($services[$service])){http_response_code(400);exit('{"ok":false}');}
campaign_session();
if(empty($_SESSION['campaign_csrf']))$_SESSION['campaign_csrf']=bin2hex(random_bytes(32));
if(empty($_SESSION['campaign_submission']))$_SESSION['campaign_submission']=bin2hex(random_bytes(16));
$attribution=[];$origin=$input['attribution']??[];
if(is_array($origin))foreach(['utm_source','utm_medium','utm_campaign','utm_content','utm_term','gclid','gbraid','wbraid'] as $key)if(isset($origin[$key])&&is_string($origin[$key]))$attribution[$key]=substr(preg_replace('/[^a-zA-Z0-9_ .+\-]/','',$origin[$key]),0,250);
if($attribution)$_SESSION['campaign_last_attribution']=['data'=>$attribution,'time'=>time()];
elseif(time()-($_SESSION['campaign_last_attribution']['time']??0)<1800)$attribution=$_SESSION['campaign_last_attribution']['data'];
$variant=$input['variant']??'';
if(is_string($variant)&&isset($services[$service][5][$variant]))$attribution['variant']=$variant;
// Persist only a known entry path: never a query string, fragment or arbitrary URL.
$landing=$input['landing']??'';$base=soto_base_path();
if(is_string($landing)){
    $path=$base!=='' && str_starts_with($landing,$base.'/') ? substr($landing,strlen($base)) : $landing;
    if($path==='/')$path='/index.php';
    if($path==='/campanas/')$path='/campanas/index.php';
    if(preg_match('~^/(?:[a-z0-9-]+\.php|campanas/(?:[a-z0-9-]+\.php)?)$~D',$path) && is_file(__DIR__.'/..'.$path)) $attribution['landing']=$path==='/index.php'?'/':$path;
}
$token=bin2hex(random_bytes(12));$_SESSION['campaign_attribution'][$token]=$attribution;
if(count($_SESSION['campaign_attribution'])>10)array_shift($_SESSION['campaign_attribution']);
echo json_encode(['ok'=>true,'csrf'=>$_SESSION['campaign_csrf'],'submission'=>$_SESSION['campaign_submission'],'attribution_token'=>$token]);
