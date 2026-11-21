<?php
function campaign_operation(PDO $db, string $key, ?string $value = null): string {
    if ($value !== null) $db->prepare('INSERT INTO operations (name,value) VALUES (?,?) ON CONFLICT(name) DO UPDATE SET value=excluded.value')->execute([$key,$value]);
    $stmt=$db->prepare('SELECT value FROM operations WHERE name=?');$stmt->execute([$key]);return (string)($stmt->fetchColumn() ?: '');
}
function campaign_health_snapshot(PDO $db, array $configured, int $now): array {
    $checks=$configured;
    try {
        $db->exec('BEGIN IMMEDIATE');
        $db->prepare('INSERT OR REPLACE INTO operations (name,value) VALUES (?,?)')->execute(['health_probe',(string)$now]);
        $db->exec('ROLLBACK');$checks['database']=true;
        $heartbeat=(int)campaign_operation($db,'worker_last_run');
        $checks['worker']=$heartbeat>0 && $now-$heartbeat<=max(300,(int)campaign_config('CAMPAIGN_WORKER_MAX_AGE',900));
        $stmt=$db->prepare("SELECT COUNT(*) FROM leads WHERE created_at < ? AND (admin_sent=0 OR confirmation_sent=0 OR brevo_synced=0)");
        $stmt->execute([gmdate('c',$now-1800)]);$checks['queue']=(int)$stmt->fetchColumn()===0;
    } catch (Throwable $e) { $checks['database']=false;$checks['worker']=false;$checks['queue']=false; }
    return ['ok'=>!in_array(false,$checks,true),'checks'=>$checks];
}
function campaign_health(?PDO $db = null): array {
    $configured=[
      'turnstile'=>(bool)(campaign_config('TURNSTILE_SITE_KEY') && campaign_config('TURNSTILE_SECRET_KEY')),
      'brevo'=>(bool)(campaign_config('BREVO_API_KEY') && (int)campaign_config('BREVO_LIST_ID')>0 && filter_var(campaign_config('CAMPAIGN_MAIL_FROM'),FILTER_VALIDATE_EMAIL)),
      'anthropic'=>(bool)campaign_config('ANTHROPIC_API_KEY'),
    ];
    try { return campaign_health_snapshot($db ?? campaign_db(),$configured,time()); }
    catch(Throwable $e) {return ['ok'=>false,'checks'=>array_merge($configured,['database'=>false,'worker'=>false,'queue'=>false])];}
}
function campaign_alert(?PDO $db, string $reason, ?callable $sender=null): bool {
    $now=time();
    if ($db && $now-(int)campaign_operation($db,'last_alert')<3600) return false;
    $sender=$sender ?? 'campaign_send';
    $message=['to'=>[campaign_config('CAMPAIGN_ALERT_TO','ads@soto-digital.com')],'reply_to'=>campaign_config('CAMPAIGN_MAIL_TO','ads@soto-digital.com'),'subject'=>'Revisar captación de Soto Digital','text'=>"El control de captación requiere revisión: ".$reason.". Comprueba el comando health y el panel del proveedor. Este aviso no contiene datos de clientes."];
    try {$sent=$sender($message,'health-alert-'.gmdate('YmdH'))===true;} catch(Throwable $e){$sent=false;}
    if($sent && $db) campaign_operation($db,'last_alert',(string)$now);
    if(!$sent) error_log('Campaign operational alert pending: check external health monitoring');
    return $sent;
}
function campaign_backup(PDO $db, string $directory, int $keep = 14): string {
    $parent=realpath(dirname($directory));
    if(!$parent) throw new RuntimeException('Backup parent unavailable');
    foreach(array_filter([realpath(__DIR__.'/..'),realpath($_SERVER['DOCUMENT_ROOT']??'')]) as $public) if($parent===$public || strpos($parent,$public.DIRECTORY_SEPARATOR)===0) throw new RuntimeException('Backups must be private');
    if(!is_dir($directory) && !mkdir($directory,0700,true)) throw new RuntimeException('Cannot create backup directory');
    $target=$directory.'/leads-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.sqlite';
    $oldMask=umask(0077);
    try {$db->exec('VACUUM INTO '.$db->quote($target));chmod($target,0600);} finally {umask($oldMask);}
    $files=glob($directory.'/leads-????????-??????-????????.sqlite')?:[];rsort($files,SORT_STRING);
    foreach(array_slice($files,max(1,$keep)) as $old) if(is_file($old) && !is_link($old)) unlink($old);
    return $target;
}
function campaign_purge(PDO $db, int $months = 12, bool $dryRun = false, ?int $now = null, ?callable $remoteDelete = null): array {
    $cutoff=(new DateTimeImmutable('@'.($now??time())))->modify('-'.max(1,$months).' months')->format('Y-m-d\TH:i:s\Z');
    $stmt=$db->prepare('SELECT id,payload FROM leads WHERE commercial_active=0 AND COALESCE(last_contact_at,created_at) < ?');$stmt->execute([$cutoff]);$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
    $count=0;$remoteFailed=0;
    foreach($rows as $row){
        if($dryRun){$count++;continue;}
        if($remoteDelete){$email=json_decode($row['payload'],true)['email']??'';if(!$remoteDelete($email)){$remoteFailed++;continue;}}
        $db->prepare('DELETE FROM leads WHERE id=?')->execute([$row['id']]);$count++;
    }
    if(!$dryRun)campaign_operation($db,'last_purge',json_encode(['at'=>gmdate('c',$now??time()),'deleted'=>$count,'remote_failed'=>$remoteFailed]));
    return ['dry_run'=>$dryRun,'eligible'=>count($rows),'deleted'=>$dryRun?0:$count,'remote_failed'=>$remoteFailed];
}
