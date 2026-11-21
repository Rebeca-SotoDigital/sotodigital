<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../includes/campaign-leads.php';
require __DIR__ . '/../includes/campaign-operations.php';
require __DIR__ . '/../includes/campaign-next-stage.php';
$command = $argv[1] ?? '';
if ($command === 'health') { $health=campaign_health(); echo json_encode($health).PHP_EOL; exit($health['ok']?0:1); }
if (!in_array($command, ['export', 'retry', 'status', 'backup', 'purge', 'contact', 'report'], true)) exit("Usage: php tools/campaign-leads.php export|retry|status\n");
try { $db = campaign_db(); } catch (Throwable $e) { campaign_alert(null,'almacenamiento no disponible'); fwrite(STDERR, "Storage unavailable\n"); exit(1); }
if ($command === 'report') {echo json_encode(campaign_weekly_report($db),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;exit;}
if ($command === 'contact') {
    $id=$argv[2]??'';if(!preg_match('/^[a-f0-9]{32}$/',$id))exit("Valid lead ID required\n");
    $active=in_array('--commercial-active',$argv,true)?1:0;
    $db->prepare('UPDATE leads SET last_contact_at=?, commercial_active=? WHERE id=?')->execute([gmdate('c'),$active,$id]);
    exit("Contact timestamp updated\n");
}
if ($command === 'purge') {
    $remote=null;
    if(filter_var(campaign_config('CAMPAIGN_PURGE_BREVO',false),FILTER_VALIDATE_BOOLEAN)){
        if(!campaign_config('BREVO_API_KEY'))exit("Brevo key required for remote purge\n");
        $remote=static function($email){if(!$email)return true;$result=campaign_http('https://api.brevo.com/v3/contacts/'.rawurlencode($email),['api-key: '.campaign_config('BREVO_API_KEY')],[],12,'DELETE');return ($result['status']>=200&&$result['status']<300)||$result['status']===404;};
    }
    echo json_encode(campaign_purge($db,(int)campaign_config('CAMPAIGN_RETENTION_MONTHS',12),in_array('--dry-run',$argv,true),null,$remote)).PHP_EOL;exit;
}
if ($command === 'backup') { echo campaign_backup($db, realpath(campaign_config('CAMPAIGN_DATA_DIR')).'/backups', (int)campaign_config('CAMPAIGN_BACKUP_KEEP',14)).PHP_EOL; exit; }
if ($command === 'status') {
    foreach ($db->query("SELECT COUNT(*) AS leads, SUM(brevo_synced=0) AS crm_pending, SUM(ai_status<>'complete') AS ai_pending, SUM(admin_state='sending' OR confirmation_state='sending') AS email_review FROM leads") as $row) echo json_encode($row) . "\n";
    exit;
}
if ($command === 'retry') {
    $lock = fopen(realpath(campaign_config('CAMPAIGN_DATA_DIR')) . '/worker.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) exit("Worker already running\n");
    @chmod(realpath(campaign_config('CAMPAIGN_DATA_DIR')) . '/worker.lock', 0600);
    $db->prepare('DELETE FROM rate_limits WHERE expires_at<=?')->execute([time()]);
    $conditions = [];
    if (campaign_config('BREVO_API_KEY') && (int)campaign_config('BREVO_LIST_ID') > 0) $conditions[] = 'brevo_synced=0';
    if (campaign_config('BREVO_API_KEY') && campaign_config('CAMPAIGN_MAIL_FROM')) $conditions[] = "(admin_sent=0 AND admin_state='pending') OR (confirmation_sent=0 AND confirmation_state='pending')";
    if (campaign_config('ANTHROPIC_API_KEY')) $conditions[] = "(ai_status<>'complete' AND ai_attempts<3)";
    if(filter_var(campaign_config('BREVO_CREATE_DEALS',false),FILTER_VALIDATE_BOOLEAN))$conditions[]="(deal_state='pending' AND brevo_synced=1 AND json_extract(payload,'$.category')='caliente')";
    $ids = $conditions ? $db->query('SELECT id FROM leads WHERE ' . implode(' OR ', $conditions) . ' ORDER BY processed_at ASC, created_at ASC, rowid ASC LIMIT 50')->fetchAll(PDO::FETCH_COLUMN) : [];
    foreach ($ids as $id) {
        try { campaign_process($db, $id); } catch (Throwable $e) { error_log('Campaign worker item pending'); }
        finally { $db->prepare('UPDATE leads SET processed_at=? WHERE id=?')->execute([time(), $id]); }
    }
    campaign_operation($db,'worker_last_run',(string)time());
    $health=campaign_health($db);
    if (!$health['checks']['queue']) campaign_alert($db,'solicitudes pendientes durante más de 30 minutos');
    flock($lock, LOCK_UN); fclose($lock);
    exit("Queue processed; inspect status and provider delivery logs\n");
}
$out = fopen('php://stdout', 'w');
fputcsv($out, ['id', 'fecha', 'servicio', 'nombre', 'email', 'movil', 'web', 'sector', 'objetivo', 'presupuesto', 'urgencia', 'puntuacion', 'potencial', 'resumen_ia', 'siguiente_paso_ia', 'mensaje', 'atribucion', 'brevo', 'email_equipo', 'email_cliente', 'estado_email_equipo', 'estado_email_cliente']);
foreach ($db->query('SELECT * FROM leads ORDER BY created_at DESC') as $row) {
    $data = json_decode($row['payload'], true); $score = campaign_stored_score($data);
    $values = [$row['id'], $row['created_at'], $row['service'], $data['name'], $data['email'], $data['phone'], $data['website'], $data['sector'] ?? '', $data['objective'] ?? '', $data['budget'] ?? '', $data['urgency'] ?? '', $score['score'], $score['category'], $row['ai_summary'], $row['ai_next_step'], $data['message'], $row['attribution'], $row['brevo_synced'], $row['admin_sent'], $row['confirmation_sent'], $row['admin_state'], $row['confirmation_state']];
    $values = array_map(static function ($v) { $v = (string)$v; return preg_match('/^[\s]*[=+@\-]/u', $v) ? "'" . $v : $v; }, $values);
    fputcsv($out, $values);
}
