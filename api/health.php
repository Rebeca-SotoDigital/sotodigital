<?php
require __DIR__.'/../includes/campaign-leads.php';
require __DIR__.'/../includes/campaign-operations.php';
header('Cache-Control: private, no-store');header('X-Robots-Tag: noindex, nofollow');header('Content-Type: text/plain; charset=UTF-8');
$expected=campaign_config('CAMPAIGN_HEALTH_TOKEN');
if (!$expected) {http_response_code(404);exit('fail');}
$auth=$_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if ($auth === '' && function_exists('getallheaders')) {
    foreach (getallheaders() as $name => $value) if (strcasecmp($name, 'Authorization') === 0) $auth = $value;
}
if(!hash_equals('Bearer '.$expected,$auth)){http_response_code(401);exit('fail');}
$health=campaign_health();http_response_code($health['ok']?200:503);echo $health['ok']?'ok':'fail';
