<?php
// Local audit harness only: never deployed or selected by a public request parameter.
if (getenv('SOTO_AUDIT_LOCAL') !== '1' || (PHP_SAPI !== 'cli' && !in_array($_SERVER['REMOTE_ADDR']??'', ['127.0.0.1','::1'],true))) return;
define('CAMPAIGN_TEST_HARNESS', true);
$GLOBALS['campaign_test_http'] = static function ($url,$headers,$payload) {
    if (strpos($url,'siteverify')!==false) return ['status'=>200,'body'=>['success'=>($payload['response']??'')==='audit-valid','action'=>'proposal','hostname'=>'127.0.0.1']];
    if (strpos($url,'anthropic.com')!==false) return ['status'=>200,'body'=>['content'=>[['type'=>'text','text'=>'{"resumen":"Solicitud de prueba interna.","siguiente_paso":"Revisar la solicitud de prueba."}']]]];
    if (strpos($url,'/smtp/email')!==false) return ['status'=>201,'body'=>['messageId'=>'audit-only']];
    if (strpos($url,'/contacts')!==false) return ['status'=>201,'body'=>['id'=>123]];
    return ['status'=>503,'body'=>[]];
};
