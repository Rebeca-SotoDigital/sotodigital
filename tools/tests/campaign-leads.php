<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../../includes/campaign-leads.php';
require __DIR__ . '/brevo-schema-fixture.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$valid = ['name' => 'Persona de prueba', 'company'=>'Empresa privada de prueba', 'email' => 'test@example.com', 'phone' => '+34 600 000 000', 'website' => 'https://example.com', 'message' => 'Prueba local sin envío real', 'privacy' => '1', 'sector' => 'servicios', 'objective' => 'consultas', 'budget' => '1000-3000', 'urgency' => 'este-mes'];
check(campaign_validate($valid)['has_website'], 'Website flag');
check(!campaign_validate(array_replace($valid, ['website' => '', 'message' => '']))['has_website'], 'Optional website/message');
foreach (['email' => "x@example.com\r\nBcc: x@example.org", 'phone' => 'x', 'website' => 'javascript:alert(1)', 'privacy' => '', 'name' => ['array'], 'company' => ['array'], 'message' => str_repeat('x', 5001), 'sector' => 'invalid', 'objective' => 'invalid', 'budget' => ['array'], 'urgency' => 'invalid'] as $field => $value) {
    try { campaign_validate(array_replace($valid, [$field => $value])); throw new RuntimeException('Invalid ' . $field . ' accepted'); } catch (InvalidArgumentException $e) {}
}
try { campaign_validate(array_replace($valid, ['company'=>str_repeat('x',151)])); throw new RuntimeException('Long company accepted'); } catch (InvalidArgumentException $e) {}
check(campaign_screened(['name' => '12345', 'email' => 'valid@example.com']), 'Numeric name');
check(campaign_screened(['name' => 'Persona', 'email' => 'test@sub.yopmail.com']), 'Temporary email subdomain');
check(!campaign_screened(['name' => 'José María', 'email' => 'test@notyopmail.com']), 'False-positive domain or accented name');
check(campaign_stored_score(array_merge($valid, ['score'=>4,'category'=>'templado','score_version'=>'previous']))['score']===4, 'Historical score recomputed');
check(campaign_score($valid)['score'] === 10 && campaign_score($valid)['category'] === 'caliente', 'Score hot');
check(campaign_score(array_replace($valid, ['budget'=>'300-1000','urgency'=>'explorando','website'=>'','phone'=>'']))['category'] === 'templado', 'Score warm boundary 4');
check(campaign_score(array_replace($valid, ['budget'=>'menos-300','urgency'=>'explorando','website'=>'','phone'=>'']))['category'] === 'frio', 'Score cold boundary 3');
check(campaign_score(array_replace($valid, ['budget' => 'mas-3000', 'urgency' => 'ahora']))['score'] === 10, 'Score maximum');
putenv('CAMPAIGN_DATA_DIR=' . realpath(__DIR__ . '/../..'));
try { campaign_db(); throw new RuntimeException('Public storage accepted'); } catch (RuntimeException $e) { check(strpos($e->getMessage(), 'Storage') !== false, 'Wrong failure'); }
$db = new PDO('sqlite::memory:'); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Simulate migration of the pre-existing schema.
$db->exec('CREATE TABLE leads (id TEXT PRIMARY KEY,created_at TEXT,service TEXT,payload TEXT,attribution TEXT,consent_version TEXT,admin_sent INTEGER DEFAULT 0,confirmation_sent INTEGER DEFAULT 0)');
campaign_migrate($db); campaign_migrate($db);
putenv('CAMPAIGN_RATE_SECRET=test-only-not-a-real-key');
for ($i=0; $i<5; $i++) check(campaign_rate_allow($db, '192.0.2.1', 1000), 'Rate accepted too few');
check(!campaign_rate_allow($db, '192.0.2.1', 1001), 'Sixth attempt accepted');
check(campaign_rate_allow($db, '192.0.2.2', 1001), 'Independent visitor blocked');
check(campaign_rate_allow($db, '192.0.2.1', 4600), 'Window not expired');
// Reproduce the real private-file case: an explicitly empty optional secret.
$privateFixture=tempnam(sys_get_temp_dir(),'soto-rate-');
file_put_contents($privateFixture,"<?php return ['CAMPAIGN_RATE_SECRET'=>'','TURNSTILE_SECRET_KEY'=>'test-turnstile-fallback'];");
$child='putenv("SOTO_CONFIG_FILE=".$argv[1]);putenv("CAMPAIGN_RATE_SECRET");putenv("TURNSTILE_SECRET_KEY");require $argv[2];$db=new PDO("sqlite::memory:");campaign_migrate($db);exit(campaign_rate_allow($db,"192.0.2.99",4600)?0:1);';
$process=proc_open([PHP_BINARY,'-r',$child,$privateFixture,__DIR__.'/../../includes/campaign-leads.php'],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
check(is_resource($process),'Cannot run isolated rate-secret regression');
$childOutput=stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
$childStatus=proc_close($process);unlink($privateFixture);
check($childStatus===0,'Empty optional private rate secret did not fall back to Turnstile: '.$childOutput);
check(strpos(json_encode($db->query('SELECT * FROM rate_limits')->fetchAll()), '192.0.2') === false, 'Raw IP stored');
campaign_store($db, 'test-id', 'google-ads', campaign_validate($valid), ['utm_source' => 'google', 'gclid' => 'click-test']);
campaign_store($db, 'test-id', 'google-ads', campaign_validate($valid), []);
check($db->query('SELECT COUNT(*) FROM leads')->fetchColumn() == 1, 'Duplicate submission');
$calls=[];
campaign_deliver($db, 'test-id', function ($message, $key) use (&$calls) { $calls[]=$key; check(stripos($message['text'], 'esta semana o este mes') !== false || strpos($key, 'confirmation') !== false, 'Urgency missing'); return strpos($key, 'admin_sent') !== false; });
$row=$db->query('SELECT * FROM leads')->fetch(PDO::FETCH_ASSOC);
check($row['admin_sent']==1 && $row['confirmation_sent']==0 && $row['confirmation_state']==='pending', 'Partial delivery state');
campaign_deliver($db, 'test-id', function ($message, $key) use (&$calls) { $calls[]=$key; check($message['to']===['test@example.com'], 'Wrong recipient'); return true; });
check(count($calls)===3, 'Delivered email retried');
campaign_deliver($db, 'test-id', function () { throw new RuntimeException('Duplicate email'); });
putenv('ANTHROPIC_API_KEY=test-not-a-real-key'); putenv('BREVO_API_KEY=xkeysib-test-not-a-real-key'); putenv('BREVO_LIST_ID=123');
$requests=[];
$http=function ($url,$headers,$payload) use (&$requests) {
    $requests[]=['url'=>$url,'payload'=>$payload];
    if (strpos($url,'anthropic.com')!==false) {
        $input=$payload['messages'][0]['content'];
        foreach (['Empresa privada de prueba','test@example.com','Persona de prueba','600 000','https://example.com','Prueba local','click-test'] as $private) check(strpos($input,$private)===false, 'Unnecessary PII sent to model');
        return ['status'=>200,'body'=>['content'=>[['type'=>'text','text'=>'{"resumen":"Empresa de servicios interesada en consultas.","siguiente_paso":"Acordar la mini-auditoría."}']]]];
    }
    if (str_ends_with($url,'/contacts/attributes')) return ['status'=>200,'body'=>['attributes'=>campaign_brevo_test_schema()]];
    if (str_contains($url,'/contacts/')) return ['status'=>404,'body'=>[]];
    check($payload['listIds']===[123] && $payload['updateEnabled']===true, 'CRM upsert');
    check(!isset($payload['emailBlacklisted']) && !isset($payload['smtpBlacklistSender']), 'Subscription state altered');
    check($payload['attributes']['URGENCIA']==='este-mes' && $payload['attributes']['PUNTUACION']===10, 'CRM scoring/urgency');
    return ['status'=>201,'body'=>['id'=>1]];
};
campaign_process($db,'test-id',$http);
$row=$db->query('SELECT * FROM leads')->fetch(PDO::FETCH_ASSOC);
check($row['ai_status']==='complete' && $row['brevo_synced']==1 && $row['ai_attempts']==1, 'Pipeline incomplete');
campaign_process($db,'test-id',function(){throw new RuntimeException('Repeated integration');});
campaign_store($db,'failed-ai','google-ads',campaign_validate($valid),[]);
$sent=0;
campaign_process($db,'failed-ai',function($url){return ['status'=>503,'body'=>[]];},function()use(&$sent){$sent++;return true;});
check($sent===2, 'AI/CRM failure blocked email');
check($db->query("SELECT COUNT(*) FROM leads WHERE id='failed-ai'")->fetchColumn()==1, 'Failed provider lost lead');
campaign_store($db,'ambiguous','google-ads',campaign_validate($valid),[]);
campaign_deliver($db,'ambiguous',function(){return null;});
campaign_deliver($db,'ambiguous',function(){throw new RuntimeException('Ambiguous send retried');});
$row=$db->query("SELECT * FROM leads WHERE id='ambiguous'")->fetch(PDO::FETCH_ASSOC);
check($row['admin_state']==='sending' && $row['admin_sent']==0, 'Ambiguous state lost');
echo "PASS: validation, optional website, screening, score boundaries, migration, persistent 5/hour rate, PII minimization, CRM upsert, duplicate protection, provider failures and email recovery\n";

$low=array_replace($valid,['budget'=>'por-definir','urgency'=>'explorando','objective'=>'orientacion','website'=>'','phone'=>'']);
check(campaign_score($low)['score']===0,'Score minimum');
$six=array_replace($valid,['budget'=>'300-1000','urgency'=>'1-3-meses','phone'=>'']);
check(campaign_score($six)['score']===6 && campaign_score($six)['category']==='templado','Score boundary six');
check(campaign_score(array_replace($six,['phone'=>'+34600000000']))['category']==='caliente','Score boundary seven');
check(campaign_validate(array_replace($valid,['phone'=>'']))['phone']==='','Optional phone');
putenv('CAMPAIGN_PHONE_REQUIRED=true');
try {campaign_validate(array_replace($valid,['phone'=>'']));throw new RuntimeException('Required phone accepted');} catch(InvalidArgumentException $e){}
putenv('CAMPAIGN_PHONE_REQUIRED=false');
echo "PASS: agreed scores 0/3/4/6/7/10 and optional/required phone switch\n";
// Never accept an apparently valid JSON when the provider reports truncation/refusal.
$aiDb=new PDO('sqlite::memory:');$aiDb->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);campaign_migrate($aiDb);
campaign_store($aiDb,'ai-budget-check','google-ads',campaign_validate($valid),[]);
$aiRow=$aiDb->query('SELECT * FROM leads')->fetch(PDO::FETCH_ASSOC);
$partial=static function($url,$headers,$payload){
 check($payload['max_tokens']>=1000&&$payload['max_tokens']<=1500,'Unsafe summary output limit');
 check(($payload['output_config']['effort']??'')==='low','Summary effort not bounded');
 return ['status'=>200,'body'=>['stop_reason'=>'max_tokens','content'=>[['type'=>'text','text'=>'{"resumen":"No aceptar truncado","siguiente_paso":"No aceptar"}']]]];
};
campaign_enrich($aiDb,$aiRow,$partial);
check($aiDb->query('SELECT ai_status FROM leads')->fetchColumn()==='pending','Truncated answer accepted');
$aiRow=$aiDb->query('SELECT * FROM leads')->fetch(PDO::FETCH_ASSOC);
campaign_enrich($aiDb,$aiRow,static function(){return ['status'=>200,'body'=>['stop_reason'=>'end_turn','content'=>[['type'=>'thinking','thinking'=>'No guardar razonamiento'],['type'=>'text','text'=>'{"resumen":"Resumen válido","siguiente_paso":"Revisión humana","score":0}']]]];});
$aiAfter=$aiDb->query('SELECT * FROM leads')->fetch(PDO::FETCH_ASSOC);
check($aiAfter['ai_status']==='complete'&&$aiAfter['ai_summary']==='Resumen válido','Valid summary not stored');
check(campaign_stored_score(json_decode($aiAfter['payload'],true))['score']===10,'AI overwrote PHP score');
echo "PASS: bounded AI output, truncated response rejection, text-block selection and PHP score unchanged\n";

campaign_store($aiDb,'ai-long-check','google-ads',campaign_validate($valid),[]);
$longRow=$aiDb->query("SELECT * FROM leads WHERE id='ai-long-check'")->fetch(PDO::FETCH_ASSOC);
campaign_enrich($aiDb,$longRow,static function(){return ['status'=>200,'body'=>['stop_reason'=>'end_turn','content'=>[['type'=>'text','text'=>json_encode(['resumen'=>str_repeat('x',241),'siguiente_paso'=>'Concretar el objetivo.'])]]]];});
check($aiDb->query("SELECT ai_status FROM leads WHERE id='ai-long-check'")->fetchColumn()==='pending','Overlong summary accepted or silently cut');
echo "PASS: concise AI limits without cutting sentences
";
