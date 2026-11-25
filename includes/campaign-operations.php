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
      'anthropic'=>!filter_var(campaign_config('CAMPAIGN_REQUIRE_AI',false),FILTER_VALIDATE_BOOLEAN) || (bool)campaign_config('ANTHROPIC_API_KEY'),
    ];
    try { $health=campaign_health_snapshot($db ?? campaign_db(),$configured,time()); $health['warnings']=campaign_config('ANTHROPIC_API_KEY')?[]:['IA no configurada; no bloquea captación/Brevo salvo CAMPAIGN_REQUIRE_AI=true.']; return $health; }
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
/** Compatibility snapshot for older SQLite engines without VACUUM INTO. */
function campaign_backup_logical(PDO $source, string $target): void {
    if($source->inTransaction()||file_exists($target))throw new RuntimeException('Snapshot requires a fresh target');
    $destination=null;$mask=umask(0077);$success=false;
    $quote=static fn($name)=>'"'.str_replace('"','""',$name).'"';
    try {
        $handle=fopen($target,'x');if(!$handle)throw new RuntimeException('Cannot reserve backup');fclose($handle);chmod($target,0600);
        $destination=new PDO('sqlite:'.$target);$destination->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $destination->exec('PRAGMA foreign_keys=OFF');
        // The first read establishes a stable source snapshot, including its schema.
        $source->beginTransaction();
        $schema=$source->query("SELECT type,name,sql FROM sqlite_master WHERE sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY CASE type WHEN 'table' THEN 0 ELSE 1 END,name")->fetchAll(PDO::FETCH_ASSOC);
        $destination->beginTransaction();
        foreach($schema as $object){
            if($object['type']!=='table')continue;
            $destination->exec($object['sql']);
            $columns=$source->query('PRAGMA table_info('.$quote($object['name']).')')->fetchAll(PDO::FETCH_ASSOC);
            $names=array_column($columns,'name');
            $insert=$destination->prepare('INSERT INTO '.$quote($object['name']).' ('.implode(',',array_map($quote,$names)).') VALUES ('.implode(',',array_fill(0,count($names),'?')).')');
            $rows=$source->query('SELECT '.implode(',',array_map($quote,$names)).' FROM '.$quote($object['name']));
            while($row=$rows->fetch(PDO::FETCH_NUM)){
                foreach($row as $i=>$value)$insert->bindValue($i+1,$value,$value===null?PDO::PARAM_NULL:(is_int($value)?PDO::PARAM_INT:PDO::PARAM_STR));
                $insert->execute();
            }
        }
        foreach($schema as $object)if($object['type']!=='table')$destination->exec($object['sql']);
        if($source->query("SELECT COUNT(*) FROM sqlite_master WHERE name='sqlite_sequence'")->fetchColumn()){
            $destination->exec('DELETE FROM sqlite_sequence');$insert=$destination->prepare('INSERT INTO sqlite_sequence(name,seq) VALUES(?,?)');
            foreach($source->query('SELECT name,seq FROM sqlite_sequence') as $row)$insert->execute([$row['name'],$row['seq']]);
        }
        $version=(int)$source->query('PRAGMA user_version')->fetchColumn();$destination->exec('PRAGMA user_version='.$version);
        $destination->commit();$source->rollBack();$success=true;
    }finally{
        if($source->inTransaction())$source->rollBack();
        if($destination&&$destination->inTransaction())$destination->rollBack();
        $destination=null;
        if(!$success)foreach([$target,$target.'-journal'] as $file)if(is_file($file))unlink($file);
        umask($mask);
    }
}
function campaign_backup(PDO $db, string $directory, int $keep = 14): string {
    $parent=realpath(dirname($directory));
    if(!$parent) throw new RuntimeException('Backup parent unavailable');
    foreach(array_filter([realpath(__DIR__.'/..'),realpath($_SERVER['DOCUMENT_ROOT']??'')]) as $public) if($parent===$public || strpos($parent,$public.DIRECTORY_SEPARATOR)===0) throw new RuntimeException('Backups must be private');
    if(!is_dir($directory) && !mkdir($directory,0700,true)) throw new RuntimeException('Cannot create backup directory');
    if(is_link($directory))throw new RuntimeException('Backup directory must not be a symlink');
    $actual=realpath($directory);
    if(!$actual)throw new RuntimeException('Backup directory unavailable');
    foreach(array_filter([realpath(__DIR__.'/..'),realpath($_SERVER['DOCUMENT_ROOT']??'')]) as $public)if($actual===$public||strpos($actual,$public.DIRECTORY_SEPARATOR)===0)throw new RuntimeException('Backups must be private');
    if(!chmod($directory,0700))throw new RuntimeException('Cannot protect backup directory');
    $target=$directory.'/leads-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.sqlite';
    $oldMask=umask(0077);
    try {
        try {$db->exec('VACUUM INTO '.$db->quote($target));}
        catch(PDOException $e){
            if(stripos($e->getMessage(),'near "INTO"')===false)throw $e;
            campaign_backup_logical($db,$target);
        }
        chmod($target,0600);
    } finally {umask($oldMask);}
    $files=glob($directory.'/leads-????????-??????-????????.sqlite')?:[];usort($files,static function($a,$b)use($target){if($a===$target)return -1;if($b===$target)return 1;return filemtime($b)<=>filemtime($a);});
    foreach(array_slice($files,max(1,$keep)) as $old) if(is_file($old) && !is_link($old)) unlink($old);
    return $target;
}
function campaign_purge(PDO $db, int $months = 12, bool $dryRun = false, ?int $now = null, ?callable $remoteDelete = null): array {
    $cutoff=(new DateTimeImmutable('@'.($now??time())))->modify('-'.max(1,$months).' months')->format('Y-m-d\TH:i:s\Z');
    $stmt=$db->prepare('SELECT id,payload FROM leads WHERE commercial_active=0 AND COALESCE(last_contact_at,created_at) < ?');$stmt->execute([$cutoff]);$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
    $count=0;$remoteFailed=0;
    foreach($rows as $row){
        if($dryRun){$count++;continue;}
        if($remoteDelete){$email=json_decode($row['payload'],true)['email']??'';$other=$db->prepare("SELECT COUNT(*) FROM leads WHERE id<>? AND lower(json_extract(payload,'$.email'))=lower(?)");$other->execute([$row['id'],$email]);if((int)$other->fetchColumn()===0 && !$remoteDelete($email)){$remoteFailed++;continue;}}
        $db->prepare('DELETE FROM leads WHERE id=?')->execute([$row['id']]);$count++;
    }
    if(!$dryRun)campaign_operation($db,'last_purge',json_encode(['at'=>gmdate('c',$now??time()),'deleted'=>$count,'remote_failed'=>$remoteFailed]));
    return ['dry_run'=>$dryRun,'eligible'=>count($rows),'deleted'=>$dryRun?0:$count,'remote_failed'=>$remoteFailed];
}
