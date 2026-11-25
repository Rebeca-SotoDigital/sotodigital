<?php
require __DIR__.'/../includes/campaign-worker.php';
header('Cache-Control: private, no-store');header('X-Robots-Tag: noindex, nofollow');header('Content-Type: application/json; charset=UTF-8');
// A separate private token; never accepted in URL, form fields or cookies.
if(strlen((string)campaign_config('CAMPAIGN_WORKER_TOKEN'))<32){http_response_code(404);exit('{"ok":false}');}
$token=$_SERVER['HTTP_X_SOTO_WORKER_TOKEN']??'';
if(!campaign_worker_authorized(is_string($token)?$token:'')){http_response_code(401);exit('{"ok":false}');}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){header('Allow: POST');http_response_code(405);exit('{"ok":false}');}
if((int)($_SERVER['CONTENT_LENGTH']??0)>1024){http_response_code(413);exit('{"ok":false}');}
ignore_user_abort(true);
try {$result=campaign_run_queue(campaign_db(),null,3,22,null,true);echo json_encode(['ok'=>true]+$result);}
catch(Throwable $e){error_log('Campaign HTTP worker unavailable');http_response_code(503);echo '{"ok":false}';}
