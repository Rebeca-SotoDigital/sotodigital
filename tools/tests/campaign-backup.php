<?php
define('CAMPAIGN_TEST_HARNESS',true);
$GLOBALS['campaign_test_http']=static function(){throw new RuntimeException('Unexpected provider call');};
require __DIR__.'/../../includes/campaign-backup.php';
function backup_assert($ok,$message){if(!$ok)throw new RuntimeException($message);}
$dir=sys_get_temp_dir().'/soto-restore-test-'.bin2hex(random_bytes(5));mkdir($dir,0700);
putenv('CAMPAIGN_DATA_DIR='.$dir);putenv('CAMPAIGN_BACKUP_KEEP=2');
try {
    $db=campaign_db();$id=str_repeat('a',32);
    $db->prepare('INSERT INTO leads(id,created_at,service,payload,attribution,consent_version) VALUES(?,?,?,?,?,?)')->execute([$id,gmdate('c'),'google-ads','{"email":"synthetic@example.com"}','{}','test']);
    $before=$db->query('SELECT * FROM leads')->fetchAll(PDO::FETCH_ASSOC);
    $held=fopen($dir.'/worker.lock','c');flock($held,LOCK_EX);
    backup_assert(campaign_verified_backup($db)['busy']??false,'Shared lock bypassed');flock($held,LOCK_UN);fclose($held);
    $r=campaign_verified_backup($db);
    backup_assert($r['integrity']==='ok'&&$r['restore_test']==='ok'&&$r['leads']===1,'Restoration failed');
    backup_assert($before===$db->query('SELECT * FROM leads')->fetchAll(PDO::FETCH_ASSOC),'Live leads changed');
    backup_assert((fileperms($dir.'/backups')&0777)===0700,'Backup directory permissions');
    backup_assert((fileperms($dir.'/backups/'.$r['backup_file'])&0777)===0600,'Backup permissions');
    backup_assert(!glob($dir.'/backups/.restore-test-*'),'Restore files left behind');
    // Exercise old-SQLite fallback even though local SQLite supports VACUUM INTO.
    campaign_backup_logical($db,$dir.'/backups/logical.sqlite');
    $logical=campaign_verify_backup($dir.'/backups/logical.sqlite');
    backup_assert($logical['leads']===1&&$logical['integrity']==='ok','Logical backup invalid');
    $logicalDb=new PDO('sqlite:'.$dir.'/backups/logical.sqlite');
    backup_assert($before===$logicalDb->query('SELECT * FROM leads')->fetchAll(PDO::FETCH_ASSOC),'Logical snapshot data changed');
    $logicalDb->prepare('UPDATE leads SET brevo_synced=1 WHERE id=?')->execute([$id]);
    backup_assert($before===$db->query('SELECT * FROM leads')->fetchAll(PDO::FETCH_ASSOC),'Logical restoration touched source');
    $logicalDb=null;
    backup_assert(campaign_verified_backup($db)['throttled']??false,'Rapid repeat not throttled');
    file_put_contents($dir.'/backups/corrupt.sqlite','invalid sqlite file');
    try{campaign_verify_backup($dir.'/backups/corrupt.sqlite');throw new RuntimeException('Corrupt snapshot accepted');}catch(CampaignBackupFailure $e){backup_assert($e->getMessage()==='integrity','Corrupt backup phase missing');}
    backup_assert(!glob($dir.'/backups/.restore-test-*'),'Failed restore files left behind');
    foreach([1,2] as $i){campaign_operation($db,'backup_last_success','0');campaign_verified_backup($db);}
    backup_assert(count(glob($dir.'/backups/leads-*.sqlite'))===2,'Retention wrong');
    echo "PASS: consistent private backup, isolated restoration/integrity/checksum, unchanged live leads, lock/throttle, retention and corrupt-snapshot cleanup; no provider calls\n";
}finally {
    $db=null;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($it as $file){if($file->isDir())rmdir($file->getPathname());else unlink($file->getPathname());}rmdir($dir);
}
