<?php
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/../../includes/campaign-leads.php';
function email_assert($ok,$message){if(!$ok)throw new RuntimeException($message);}
$row=['service'=>'google-ads','ai_summary'=>'Resumen interno reservado','ai_next_step'=>'Siguiente interno reservado'];
$data=['name'=>'<script>alert(1)</script>','company'=>'<b>Empresa</b>','email'=>'test@example.com','phone'=>'','website'=>'https://example.com','sector'=>'servicios','objective'=>'consultas','budget'=>'1000-3000','urgency'=>'ahora','message'=>'<img src=x onerror=alert(1)>'];
$messages=campaign_email_messages($row,$data,campaign_score($data),'ads@soto-digital.com');
email_assert($messages['confirmation_sent']['to']===['test@example.com'],'Confirmation recipient');
email_assert($messages['admin_sent']['to']===['ads@soto-digital.com'],'Admin recipient');
email_assert(!str_contains($messages['confirmation_sent']['html'],'Resumen interno reservado')&&!str_contains($messages['confirmation_sent']['html'],'/10'),'Internal scoring leaked to requester');
foreach($messages as $message){email_assert(!str_contains($message['html'],'<script>')&&!str_contains($message['html'],'<img src=x'),'User HTML not escaped');email_assert(str_contains($message['html'],'max-width:600px'),'Missing responsive email container');}
email_assert(str_contains($messages['admin_sent']['html'],'&lt;img'),'Context not escaped');
email_assert(str_contains($messages['confirmation_sent']['text'],'no te hemos suscrito'),'Newsletter clarification');
email_assert(!str_contains($messages['confirmation_sent']['text'],'659 625'),'Unrequested public phone');
email_assert(str_contains($messages['confirmation_sent']['html'],'2 días laborables')&&str_contains($messages['confirmation_sent']['text'],'2 días laborables'),'Approved first-response deadline missing');
email_assert(str_contains($messages['confirmation_sent']['text'],'por email con una propuesta')&&!str_contains($messages['confirmation_sent']['text'],'te llamaremos'),'No-phone confirmation promises a call');
echo "PASS: email recipients, HTML escaping, responsive structure and separation of internal scoring\n";
define('CAMPAIGN_TEST_HARNESS',true);putenv('BREVO_API_KEY=xkeysib-test-only');putenv('CAMPAIGN_MAIL_FROM=ads@soto-digital.com');
$GLOBALS['campaign_test_http']=static function($url,$headers,$payload){
 email_assert(str_ends_with($url,'/smtp/email'),'Unexpected email endpoint');
 email_assert(isset($payload['htmlContent'])&&!isset($payload['textContent']),'Invalid body combination');
 email_assert($payload['replyTo']['email']==='ads@soto-digital.com','Reply address');
 return ['status'=>201,'body'=>['messageId'=>'mock-only']];
};
email_assert(campaign_send($messages['confirmation_sent'],'test-template'),'HTML email not accepted by transport fixture');
echo "PASS: HTML email transport and reply-to, no real sends\n";
