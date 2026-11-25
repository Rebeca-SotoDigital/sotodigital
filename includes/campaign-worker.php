<?php
require_once __DIR__.'/campaign-leads.php';
require_once __DIR__.'/campaign-operations.php';

function campaign_worker_authorized(string $provided): bool {
    $expected=(string)campaign_config('CAMPAIGN_WORKER_TOKEN');
    return strlen($expected)>=32 && hash_equals($expected,$provided);
}

/** Shared lock with the CLI worker; bounded batches, no personal data returned. */
function campaign_run_queue(PDO $db, ?string $onlyId=null, int $limit=3, int $seconds=22, ?callable $processor=null, bool $scheduled=false): array {
    $directory=realpath(campaign_config('CAMPAIGN_DATA_DIR'));
    if(!$directory)throw new RuntimeException('Private storage unavailable');
    $lock=fopen($directory.'/worker.lock','c');
    if(!$lock)throw new RuntimeException('Worker lock unavailable');
    if(!flock($lock,LOCK_EX|LOCK_NB)){fclose($lock);return ['busy'=>true,'processed'=>0];}
    @chmod($directory.'/worker.lock',0600);
    $previous=$GLOBALS['campaign_deadline']??null;
    try {
        if($scheduled && time()-(int)campaign_operation($db,'worker_http_last_run')<30)return ['throttled'=>true,'processed'=>0];
        if($scheduled)campaign_operation($db,'worker_http_last_run',(string)time());
        $GLOBALS['campaign_deadline']=microtime(true)+max(2,min(25,$seconds));
        $conditions=[];
        if(campaign_config('BREVO_API_KEY')&&(int)campaign_config('BREVO_LIST_ID')>0)$conditions[]='brevo_synced=0';
        if(campaign_config('BREVO_API_KEY')&&campaign_config('CAMPAIGN_MAIL_FROM'))$conditions[]="(admin_sent=0 AND admin_state='pending') OR (confirmation_sent=0 AND confirmation_state='pending')";
        if(campaign_config('ANTHROPIC_API_KEY'))$conditions[]="(ai_status<>'complete' AND ai_attempts<3)";
        $limit=max(1,min(5,$limit));
        if($onlyId!==null){
            if(!preg_match('/^[a-f0-9]{32}$/D',$onlyId))throw new InvalidArgumentException('Invalid request ID');
            $q=$db->prepare('SELECT id FROM leads WHERE id=?');$q->execute([$onlyId]);$ids=$q->fetchAll(PDO::FETCH_COLUMN);
        } else $ids=$conditions?$db->query('SELECT id FROM leads WHERE ('.implode(' OR ',$conditions).') ORDER BY processed_at ASC,created_at ASC,rowid ASC LIMIT '.$limit)->fetchAll(PDO::FETCH_COLUMN):[];
        $count=0;
        foreach($ids as $id){
            if(!campaign_request_budget())break;
            try {
                if($processor)$processor($db,$id);
                elseif($onlyId!==null){
                    // Immediate capture prioritises CRM and emails, without waiting for AI.
                    $q=$db->prepare('SELECT * FROM leads WHERE id=?');$q->execute([$id]);
                    try {campaign_brevo_sync($db,$q->fetch(PDO::FETCH_ASSOC));}catch(Throwable $e){error_log('Campaign immediate CRM pending');}
                    campaign_deliver($db,$id);
                } else campaign_process($db,$id);
            }catch(Throwable $e){error_log('Campaign HTTP worker item pending');}
            finally {$db->prepare('UPDATE leads SET processed_at=? WHERE id=?')->execute([time(),$id]);}
            $count++;
        }
        $db->prepare('DELETE FROM rate_limits WHERE expires_at<=?')->execute([time()]);
        // Only the scheduled full-queue path certifies worker activity.
        if($scheduled)campaign_operation($db,'worker_last_run',(string)time());
        $pending=(int)$db->query("SELECT COUNT(*) FROM leads WHERE brevo_synced=0 OR admin_sent=0 OR confirmation_sent=0 OR (ai_status<>'complete' AND ai_attempts<3)")->fetchColumn();
        $review=(int)$db->query("SELECT COUNT(*) FROM leads WHERE admin_state='sending' OR confirmation_state='sending'")->fetchColumn();
        return ['busy'=>false,'processed'=>$count,'pending'=>$pending,'email_review'=>$review];
    } finally {
        if($previous===null)unset($GLOBALS['campaign_deadline']);else $GLOBALS['campaign_deadline']=$previous;
        flock($lock,LOCK_UN);fclose($lock);
    }
}
