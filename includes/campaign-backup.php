<?php
require_once __DIR__.'/campaign-worker.php';
class CampaignBackupFailure extends RuntimeException {}

/** Reopen a copy in isolation; never restore over the live lead database. */
function campaign_verify_backup(string $backup): array {
    $directory=dirname($backup);
    $test=$directory.'/.restore-test-'.bin2hex(random_bytes(8)).'.sqlite';
    $mask=umask(0077);$snapshot=null;$restored=null;
    $stage='copy';
    try {
        if(!is_file($backup)||is_link($backup)||!copy($backup,$test))throw new RuntimeException('Restore fixture unavailable');
        chmod($test,0600);
        $sha=hash_file('sha256',$backup);
        if(!$sha||!hash_equals($sha,hash_file('sha256',$test)))throw new RuntimeException('Restore checksum mismatch');
        $stage='integrity';
        $snapshot=new PDO('sqlite:'.$backup);$restored=new PDO('sqlite:'.$test);
        foreach([$snapshot,$restored] as $db){
            $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$db->exec('PRAGMA query_only=ON');
            if($db->query('PRAGMA integrity_check')->fetchColumn()!=='ok')throw new RuntimeException('Backup integrity failed');
        }
        $stage='tables';
        foreach(['leads','operations','rate_limits'] as $table){
            $a=(int)$snapshot->query('SELECT COUNT(*) FROM '.$table)->fetchColumn();
            $b=(int)$restored->query('SELECT COUNT(*) FROM '.$table)->fetchColumn();
            if($a!==$b)throw new RuntimeException('Restore row count mismatch');
        }
        return ['integrity'=>'ok','restore_test'=>'ok','leads'=>(int)$restored->query('SELECT COUNT(*) FROM leads')->fetchColumn(),'sha256'=>$sha];
    }catch(Throwable $e){throw new CampaignBackupFailure($stage,0,$e);}finally{
        $snapshot=null;$restored=null;
        foreach([$test,$test.'-journal',$test.'-wal',$test.'-shm'] as $file)if(is_file($file))unlink($file);
        umask($mask);
    }
}

function campaign_verified_backup(PDO $db): array {
    $directory=realpath(campaign_config('CAMPAIGN_DATA_DIR'));
    if(!$directory)throw new RuntimeException('Storage unavailable');
    $lock=fopen($directory.'/worker.lock','c');if(!$lock)throw new RuntimeException('Lock unavailable');
    if(!flock($lock,LOCK_EX|LOCK_NB)){fclose($lock);return ['busy'=>true];}
    $stage='snapshot';
    try {
        // Avoid repeated copies from rapid test clicks; a daily schedule is intended.
        if(time()-(int)campaign_operation($db,'backup_last_success')<300)return ['throttled'=>true];
        // Keep old backups until the new snapshot has passed restoration checks.
        $backup=campaign_backup($db,$directory.'/backups',PHP_INT_MAX);
        $stage='verification';
        $report=campaign_verify_backup($backup);
        $stage='rotation';
        // Cap existing configurations too: retain at most the three newest verified copies.
        $keep=max(2,min(3,(int)campaign_config('CAMPAIGN_BACKUP_KEEP',3)));
        $files=glob($directory.'/backups/leads-????????-??????-????????.sqlite')?:[];
        usort($files,static function($a,$b)use($backup){if($a===$backup)return -1;if($b===$backup)return 1;return filemtime($b)<=>filemtime($a);});
        foreach(array_slice($files,$keep) as $file)if(is_file($file)&&!is_link($file))unlink($file);
        campaign_operation($db,'backup_last_success',(string)time());
        return ['busy'=>false,'backup_file'=>basename($backup),'retention_limit'=>$keep]+$report;
    }catch(CampaignBackupFailure $e){throw $e;}
    catch(Throwable $e){throw new CampaignBackupFailure($stage,0,$e);}
    finally{flock($lock,LOCK_UN);fclose($lock);}
}
