<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__ . '/../../includes/campaign-leads.php';
function audit_check($condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$s = ['REMOTE_ADDR'=>'192.0.2.8','HTTP_CF_CONNECTING_IP'=>'198.51.100.9','HTTP_X_FORWARDED_FOR'=>'203.0.113.9'];
audit_check(campaign_client_ip($s,[])==='192.0.2.8','Untrusted IP spoofing');
audit_check(campaign_client_ip($s,['192.0.2.0/24'])==='198.51.100.9','Trusted CF proxy');
unset($s['HTTP_CF_CONNECTING_IP']);$s['HTTP_X_FORWARDED_FOR']='invalid, 2001:db8::1, 203.0.113.1';
audit_check(campaign_client_ip($s,['192.0.2.0/24'])==='2001:db8::1','Forwarded IPv6');
audit_check(campaign_ip_in_cidr('2001:db8::2','2001:db8::/32'),'IPv6 CIDR');
audit_check(!campaign_ip_in_cidr('2001:db9::2','2001:db8::/32'),'IPv6 CIDR outside');
audit_check(!campaign_ip_in_cidr('192.0.2.1','192.0.2.0/999'),'Bad CIDR');
echo "PASS: trusted proxies, spoof protection, CIDR IPv4/IPv6\n";
require __DIR__.'/../../includes/campaign-operations.php';
$db=new PDO('sqlite::memory:');$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);campaign_migrate($db);
$now=time();$configured=['turnstile'=>true,'brevo'=>true,'anthropic'=>true];
audit_check(!campaign_health_snapshot($db,$configured,$now)['checks']['worker'],'Stopped worker accepted');
campaign_operation($db,'worker_last_run',(string)$now);
audit_check(campaign_health_snapshot($db,$configured,$now)['ok'],'Healthy queue rejected');
$db->prepare('INSERT INTO leads (id,created_at,service,payload,attribution,consent_version) VALUES (?,?,?,?,?,?)')->execute(['stale',gmdate('c',$now-3600),'google-ads','{}','{}','test']);
audit_check(!campaign_health_snapshot($db,$configured,$now)['checks']['queue'],'Stale queue missed');
$alerts=0;campaign_alert($db,'test',function($message)use(&$alerts){$alerts++;return true;});campaign_alert($db,'test',function()use(&$alerts){$alerts++;return true;});
audit_check($alerts===1,'Alert not generated or repeated');
$configured['brevo']=false;audit_check(!campaign_health_snapshot($db,$configured,$now)['ok'],'Missing Brevo accepted');
$db->exec('PRAGMA query_only=ON');audit_check(!campaign_health_snapshot($db,$configured,$now)['checks']['database'],'Read-only storage accepted');
echo "PASS: stopped worker, stale queue/Brevo failure, throttled alert and read-only storage\n";
$db->exec('PRAGMA query_only=OFF');
$backupDir=sys_get_temp_dir().'/soto-backup-test-'.bin2hex(random_bytes(4));
$backup=campaign_backup($db,$backupDir,1);
$copy=new PDO('sqlite:'.$backup);audit_check((int)$copy->query('SELECT COUNT(*) FROM leads')->fetchColumn()===1,'Inconsistent backup');$copy=null;
audit_check((fileperms($backup)&0777)===0600,'Public backup permissions');
$backup2=campaign_backup($db,$backupDir,1);audit_check(is_file($backup2),'Newest backup missing');audit_check(count(glob($backupDir.'/*.sqlite'))===1,'Backup rotation');
foreach(glob($backupDir.'/*') as $file)unlink($file);rmdir($backupDir);
echo "PASS: consistent private SQLite backup and rotation\n";
$purgeDb=new PDO('sqlite::memory:');$purgeDb->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);campaign_migrate($purgeDb);
foreach(['old','active','recent-contact','fresh'] as $id){$purgeDb->prepare('INSERT INTO leads(id,created_at,service,payload,attribution,consent_version) VALUES(?,?,?,?,?,?)')->execute([$id,$id==='fresh'?gmdate('c',$now):gmdate('c',$now-400*86400),'google-ads',json_encode(['email'=>$id.'@example.com']),'{"gclid":"private"}','test']);}
$purgeDb->exec("UPDATE leads SET commercial_active=1 WHERE id='active'");$purgeDb->prepare("UPDATE leads SET last_contact_at=? WHERE id='recent-contact'")->execute([gmdate('c',$now)]);
audit_check(campaign_purge($purgeDb,12,true,$now)['eligible']===1,'Purge dry-run selection');
audit_check(campaign_purge($purgeDb,12,false,$now,static function(){return false;})['remote_failed']===1,'Failed remote deletion lost local reference');
audit_check(campaign_purge($purgeDb,12,false,$now)['deleted']===1,'Purge count');
audit_check((int)$purgeDb->query('SELECT COUNT(*) FROM leads')->fetchColumn()===3,'Commercial/recent lead removed');
echo "PASS: retention, dry-run, commercial holds, last contact and failed remote cleanup\n";
require __DIR__.'/../../includes/campaign-next-stage.php';
$report=campaign_weekly_report($purgeDb,$now);
audit_check(strpos(json_encode($report),'@example.com')===false && strpos(json_encode($report),'private')===false,'Report personal data');
$lead=$purgeDb->query('SELECT * FROM leads LIMIT 1')->fetch(PDO::FETCH_ASSOC);
campaign_create_deal($purgeDb,$lead,static function(){throw new RuntimeException('Disabled CRM called');});
echo "PASS: aggregate-only report and CRM disabled by default\n";
// Enabled path remains entirely simulated: no real provider or account IDs.
putenv('BREVO_CREATE_DEALS=true');putenv('BREVO_API_KEY=xkeysib-audit-test');putenv('BREVO_PIPELINE_ID=audit-pipeline');putenv('BREVO_NEW_STAGE_ID=audit-new');
$hot=['name'=>'Synthetic audit','email'=>'audit@example.com','score'=>10,'category'=>'caliente','score_version'=>'v1-acordada-2026-11-21'];
$purgeDb->prepare('INSERT INTO leads(id,created_at,service,payload,attribution,consent_version,brevo_synced,brevo_contact_id) VALUES(?,?,?,?,?,?,1,123)')->execute(['hot-deal',gmdate('c'),'google-ads',json_encode($hot),'{}','test']);
$dealCalls=0;$transport=static function($url,$headers,$payload)use(&$dealCalls){$dealCalls++;audit_check(str_ends_with($url,'/crm/deals'),'Wrong deal endpoint');audit_check($payload['linkedContactsIds']===[123]&&$payload['attributes']['pipeline']==='audit-pipeline'&&$payload['attributes']['deal_stage']==='audit-new','Wrong CRM association');return ['status'=>201,'body'=>['id'=>'audit-deal']];};
foreach([1,2] as $attempt){$row=$purgeDb->query("SELECT * FROM leads WHERE id='hot-deal'")->fetch(PDO::FETCH_ASSOC);campaign_create_deal($purgeDb,$row,$transport);}
audit_check($dealCalls===1,'Repeated deal created');putenv('BREVO_CREATE_DEALS');putenv('BREVO_API_KEY');putenv('BREVO_PIPELINE_ID');putenv('BREVO_NEW_STAGE_ID');
echo "PASS: simulated hot-lead CRM association and duplicate prevention\n";
