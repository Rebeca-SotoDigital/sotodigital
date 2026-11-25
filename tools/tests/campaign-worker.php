<?php
define('CAMPAIGN_TEST_HARNESS',true);
require __DIR__.'/../../includes/campaign-worker.php';
require __DIR__.'/brevo-schema-fixture.php';
function worker_check($ok,$message){if(!$ok)throw new RuntimeException($message);}
$dir=sys_get_temp_dir().'/soto-worker-test-'.bin2hex(random_bytes(5));mkdir($dir,0700);
putenv('CAMPAIGN_DATA_DIR='.$dir);putenv('CAMPAIGN_WORKER_TOKEN='.str_repeat('t',40));
putenv('BREVO_API_KEY=xkeysib-test-worker');putenv('BREVO_LIST_ID=1');putenv('CAMPAIGN_MAIL_FROM=ads@example.com');putenv('CAMPAIGN_MAIL_TO=ads@example.com');putenv('ANTHROPIC_API_KEY=fixture');
$calls=[];$GLOBALS['campaign_test_http']=static function($url,$headers,$payload)use(&$calls){
    $calls[]=$url;
    if(str_contains($url,'anthropic.com'))return ['status'=>200,'body'=>['stop_reason'=>'end_turn','content'=>[['type'=>'text','text'=>'{"resumen":"Negocio interesado en captar consultas.","siguiente_paso":"Revisar sus objetivos para preparar la propuesta."}']]]];
    if(str_ends_with($url,'/contacts/attributes'))return ['status'=>200,'body'=>['attributes'=>campaign_brevo_test_schema()]];
    if(str_contains($url,'/contacts/'))return ['status'=>404,'body'=>[]];
    return ['status'=>201,'body'=>['id'=>123]];
};
try {
    worker_check(campaign_worker_authorized(str_repeat('t',40)),'Token rejected');
    worker_check(!campaign_worker_authorized('wrong'),'Wrong token accepted');
    // Exercise public entry point rejection before opening storage or calling providers.
    foreach([
        ['GET','',401],
        ['POST','wrong',401],
        ['GET',str_repeat('t',40),405],
    ] as [$method,$token,$expected]){
        $code='$_SERVER["REQUEST_METHOD"]='.var_export($method,true).';$_SERVER["HTTP_X_SOTO_WORKER_TOKEN"]='.var_export($token,true).';$_GET["token"]='.var_export(str_repeat('t',40),true).';register_shutdown_function(function(){fwrite(STDERR,(string)http_response_code());});require '.var_export(__DIR__.'/../../api/worker.php',true).';';
        $p=proc_open([PHP_BINARY,'-r',$code],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
        $out=stream_get_contents($pipes[1]);$status=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($p);
        worker_check($status===(string)$expected&&$out==='{"ok":false}','Endpoint auth or method failed');
    }
    putenv('CAMPAIGN_WORKER_TOKEN=short');worker_check(!campaign_worker_authorized('short'),'Weak token accepted');putenv('CAMPAIGN_WORKER_TOKEN='.str_repeat('t',40));
    $db=new PDO('sqlite::memory:');$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);campaign_migrate($db);
    $id=str_repeat('a',32);$data=campaign_validate(['name'=>'Synthetic test','email'=>'test@example.com','phone'=>'','website'=>'','company'=>'','message'=>'','privacy'=>'1','sector'=>'servicios','objective'=>'consultas','budget'=>'300-1000','urgency'=>'este-mes']);
    campaign_store($db,$id,'google-ads',$data,[]);
    $held=fopen($dir.'/worker.lock','c');flock($held,LOCK_EX);
    worker_check(campaign_run_queue($db,$id)['busy']===true,'Concurrent worker accepted');flock($held,LOCK_UN);fclose($held);
    $result=campaign_run_queue($db,$id);$row=$db->query('SELECT * FROM leads')->fetch(PDO::FETCH_ASSOC);
    worker_check($result['processed']===1&&$row['brevo_synced']==1&&$row['admin_sent']==1&&$row['confirmation_sent']==1,'Immediate capture not processed');
    worker_check($row['ai_status']==='pending'&&!array_filter($calls,fn($url)=>str_contains($url,'anthropic.com')),'Immediate capture waited for AI');
    worker_check(campaign_operation($db,'worker_last_run')==='','Immediate capture certified scheduled worker');
    $emails=count(array_filter($calls,fn($url)=>str_contains($url,'/smtp/email')));
    campaign_run_queue($db,null,3,22,null,true);$row=$db->query('SELECT * FROM leads')->fetch(PDO::FETCH_ASSOC);
    worker_check($row['ai_status']==='complete'&&$row['brevo_synced']==1,'Scheduled AI and resync incomplete');
    worker_check(count(array_filter($calls,fn($url)=>str_contains($url,'/smtp/email')))===$emails,'Emails duplicated on enrichment');
    worker_check(campaign_run_queue($db,null,3,22,null,true)['throttled']??false,'Scheduled throttle absent');
    worker_check(!isset($GLOBALS['campaign_deadline']),'Deadline leaked');
    $GLOBALS['campaign_deadline']=microtime(true)-1;$before=count($calls);
    try{campaign_http('https://example.invalid',[],[]);throw new RuntimeException('Expired budget used');}catch(RuntimeException $e){worker_check($e->getMessage()==='Campaign processing budget exhausted','Wrong budget exception');}
    worker_check(count($calls)===$before,'Expired budget performed request');unset($GLOBALS['campaign_deadline']);
    echo "PASS: immediate CRM/emails, deferred AI/resync, no duplicate emails, shared worker lock, secret validation, throttling and request budgets; no real network\n";
}finally{unset($GLOBALS['campaign_deadline']);unlink($dir.'/worker.lock');rmdir($dir);}
