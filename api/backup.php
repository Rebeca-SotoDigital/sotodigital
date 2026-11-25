<?php
require __DIR__.'/../includes/campaign-backup.php';
header('Cache-Control: private, no-store');header('X-Robots-Tag: noindex, nofollow');header('Content-Type: application/json; charset=UTF-8');
if(strlen((string)campaign_config('CAMPAIGN_WORKER_TOKEN'))<32){http_response_code(404);exit('{"ok":false}');}
$token=$_SERVER['HTTP_X_SOTO_WORKER_TOKEN']??'';
if(!campaign_worker_authorized(is_string($token)?$token:'')){http_response_code(401);exit('{"ok":false}');}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){header('Allow: POST');http_response_code(405);exit('{"ok":false}');}
ignore_user_abort(true);
try {
    $report=campaign_verified_backup(campaign_db());
    if(!empty($report['busy'])||!empty($report['throttled'])){header('Retry-After: 300');http_response_code(429);echo '{"ok":false,"retry_later":true}';}
    else echo json_encode(['ok'=>true]+$report);
}catch(Throwable $e){
    $stage=$e instanceof CampaignBackupFailure?$e->getMessage():'storage';
    if(!in_array($stage,['storage','snapshot','copy','integrity','tables','verification','rotation'],true))$stage='unknown';
    $cause=$e->getPrevious()??$e;
    $reason='operation_failed';
    if($cause instanceof PDOException){
        $message=$cause->getMessage();
        if(stripos($message,'near "INTO"')!==false)$reason='sqlite_vacuum_into_unsupported';
        elseif(stripos($message,'no such table')!==false)$reason='missing_table';
        elseif(stripos($message,'readonly')!==false)$reason='storage_read_only';
        elseif(stripos($message,'locked')!==false)$reason='database_locked';
        else $reason='sqlite_error';
    }elseif(stripos($cause->getMessage(),'protect')!==false||stripos($cause->getMessage(),'directory')!==false)$reason='directory_or_permissions';
    error_log('Campaign backup failed: '.$stage.' / '.$reason);
    http_response_code(503);echo json_encode(['ok'=>false,'stage'=>$stage,'reason'=>$reason]);
}
