<?php
require_once __DIR__ . '/campaign-config.php';
require_once __DIR__ . '/campaign-network.php';
function campaign_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('soto_campaign');
        session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/']);
        session_start();
    }
    header('Cache-Control: private, no-store');
}
function campaign_ready(): bool {
    // Capture must survive missing/failed AI, CRM and email providers.
    return (bool)(campaign_config('TURNSTILE_SITE_KEY') && campaign_config('TURNSTILE_SECRET_KEY') && campaign_config('CAMPAIGN_DATA_DIR'));
}
function campaign_options(): array { static $options; return $options ?? ($options = require __DIR__ . '/campaign-questionnaire.php'); }
function campaign_phone_required(): bool { return filter_var(campaign_config('CAMPAIGN_PHONE_REQUIRED', false), FILTER_VALIDATE_BOOLEAN); }
function campaign_validate(array $input): array {
    $limits = ['name' => 150, 'email' => 254, 'phone' => 30, 'website' => 500, 'message' => 5000];
    $clean = [];
    foreach ($limits as $key => $max) {
        $value = $input[$key] ?? '';
        if (!is_string($value) || strlen(trim($value)) > $max || (!in_array($key, campaign_phone_required() ? ['website','message'] : ['website','message','phone'], true) && trim($value) === '')) throw new InvalidArgumentException('Revisa los campos obligatorios y su longitud.');
        if (!mb_check_encoding($value, 'UTF-8')) throw new InvalidArgumentException('Revisa los caracteres introducidos.');
        $clean[$key] = trim($value);
    }
    if (!filter_var($clean['email'], FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $clean['email'])) throw new InvalidArgumentException('Introduce un email válido.');
    if ($clean['phone'] !== '' && (!preg_match('/^[+0-9 ().-]{7,30}$/', $clean['phone']) || strlen(preg_replace('/\D/', '', $clean['phone'])) < 7)) throw new InvalidArgumentException('Introduce un teléfono válido.');
    if ($clean['website'] !== '') {
        $url = parse_url($clean['website']);
        if (!filter_var($clean['website'], FILTER_VALIDATE_URL) || !in_array($url['scheme'] ?? '', ['http', 'https'], true) || empty($url['host'])) throw new InvalidArgumentException('Introduce la URL con https:// o déjala vacía si no tienes web.');
    }
    foreach (['sector', 'objective', 'budget', 'urgency'] as $key) {
        $value = $input[$key] ?? '';
        if (!is_string($value) || !isset(campaign_options()[$key]['options'][$value])) throw new InvalidArgumentException('Completa las preguntas de sector, objetivo, inversión y plazo.');
        $clean[$key] = $value;
    }
    if (($input['privacy'] ?? '') !== '1') throw new InvalidArgumentException('Confirma la información de privacidad para gestionar tu solicitud.');
    $clean['has_website'] = $clean['website'] !== '';
    return $clean;
}
function campaign_screened(array $input): bool {
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    if (is_string($name) && !preg_match('/\p{L}/u', $name)) return true;
    if (!is_string($email)) return false;
    $domain = strtolower(substr(strrchr(trim($email), '@') ?: '', 1));
    $blocked = campaign_config('CAMPAIGN_DISPOSABLE_DOMAINS', ['mailinator.com', 'yopmail.com', 'yopmail.fr', 'yopmail.net', 'guerrillamail.com', 'guerrillamail.net', 'sharklasers.com', 'tempmail.com', '10minutemail.com', 'dispostable.com', 'getnada.com', 'trashmail.com']);
    if (is_string($blocked)) $blocked = array_filter(array_map('trim', explode(',', $blocked)));
    if (!is_array($blocked)) $blocked = [];

    foreach ($blocked as $host) {
        if (!is_string($host) || trim($host) === '') continue;
        $host = strtolower(trim($host));
        if ($domain === $host || substr($domain, -strlen('.' . $host)) === '.' . $host) return true;
    }
    return false;
}
function campaign_score(array $data): array {
    $rules = campaign_options()['scoring']; $score = 0;
    foreach (['budget', 'urgency', 'objective'] as $key) $score += $rules[$key][$data[$key] ?? ''] ?? 0;
    $score += !empty($data['website']) ? 1 : 0;
    $score += !empty($data['phone']) ? 1 : 0;
    $score = max(0, min(10, $score));
    return ['score' => $score, 'category' => $score >= 7 ? 'caliente' : ($score >= 4 ? 'templado' : 'frio'), 'score_version' => $rules['version']];
}
function campaign_stored_score(array $data): array {
    if (isset($data['score'], $data['category'], $data['score_version']) && is_int($data['score']) && $data['score'] >= 0 && $data['score'] <= 10 && in_array($data['category'], ['caliente', 'templado', 'frio'], true)) {
        return array_intersect_key($data, array_flip(['score', 'category', 'score_version']));
    }
    return campaign_score($data);
}
function campaign_migrate(PDO $db): void {
    $db->exec('BEGIN IMMEDIATE');
    try {
    $db->exec('CREATE TABLE IF NOT EXISTS leads (id TEXT PRIMARY KEY, created_at TEXT NOT NULL, service TEXT NOT NULL, payload TEXT NOT NULL, attribution TEXT NOT NULL, consent_version TEXT NOT NULL, admin_sent INTEGER NOT NULL DEFAULT 0, confirmation_sent INTEGER NOT NULL DEFAULT 0)');
    $columns = array_column($db->query('PRAGMA table_info(leads)')->fetchAll(PDO::FETCH_ASSOC), 'name');
    foreach (['processed_at' => 'INTEGER NOT NULL DEFAULT 0', 'brevo_synced' => 'INTEGER NOT NULL DEFAULT 0', 'ai_attempts' => 'INTEGER NOT NULL DEFAULT 0', 'ai_status' => "TEXT NOT NULL DEFAULT 'pending'", 'ai_summary' => "TEXT NOT NULL DEFAULT ''", 'ai_next_step' => "TEXT NOT NULL DEFAULT ''", 'admin_state' => "TEXT NOT NULL DEFAULT 'pending'", 'confirmation_state' => "TEXT NOT NULL DEFAULT 'pending'"] as $name => $type) {
        if (!in_array($name, $columns, true)) $db->exec('ALTER TABLE leads ADD COLUMN ' . $name . ' ' . $type);
    }
    $db->exec('CREATE TABLE IF NOT EXISTS rate_limits (fingerprint TEXT PRIMARY KEY, attempts INTEGER NOT NULL, expires_at INTEGER NOT NULL)');
    $db->exec('CREATE TABLE IF NOT EXISTS operations (name TEXT PRIMARY KEY, value TEXT NOT NULL)');
    $db->exec('COMMIT');
    } catch (Throwable $e) { $db->exec('ROLLBACK'); throw $e; }
}
function campaign_db(): PDO {
    $dir = realpath(campaign_config('CAMPAIGN_DATA_DIR'));
    if (!$dir || !is_writable($dir)) throw new RuntimeException('Private storage unavailable');
    foreach (array_filter([realpath(__DIR__ . '/..'), realpath($_SERVER['DOCUMENT_ROOT'] ?? '')]) as $root) {
        if ($dir === $root || strpos($dir, $root . DIRECTORY_SEPARATOR) === 0) throw new RuntimeException('Storage must be outside public directories');
    }
    $db = new PDO('sqlite:' . $dir . '/campaign-leads.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    @chmod($dir . '/campaign-leads.sqlite', 0600);
    $db->exec('PRAGMA busy_timeout=5000'); campaign_migrate($db);
    return $db;
}
function campaign_rate_allow(PDO $db, string $ip, ?int $now = null): bool {
    $now = $now ?? time();
    $secret = campaign_config('CAMPAIGN_RATE_SECRET', campaign_config('TURNSTILE_SECRET_KEY'));
    if (!$secret) throw new RuntimeException('Rate limit secret unavailable');
    $fingerprint = hash_hmac('sha256', $ip, $secret);
    $db->exec('BEGIN IMMEDIATE');
    try {
        $db->prepare('DELETE FROM rate_limits WHERE expires_at <= ?')->execute([$now]);
        $stmt = $db->prepare('SELECT attempts FROM rate_limits WHERE fingerprint = ?'); $stmt->execute([$fingerprint]);
        $attempts = $stmt->fetchColumn();
        if ($attempts !== false && (int)$attempts >= 5) { $db->exec('COMMIT'); return false; }
        $db->prepare('INSERT INTO rate_limits (fingerprint,attempts,expires_at) VALUES (?,1,?) ON CONFLICT(fingerprint) DO UPDATE SET attempts=attempts+1')->execute([$fingerprint, $now + 3600]);
        $db->exec('COMMIT'); return true;
    } catch (Throwable $e) { $db->exec('ROLLBACK'); throw $e; }
}
function campaign_store(PDO $db, string $id, string $service, array $data, array $attribution): void {
    $data = array_merge($data, campaign_score($data));
    $stmt = $db->prepare('INSERT OR IGNORE INTO leads (id,created_at,service,payload,attribution,consent_version) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$id, gmdate('c'), $service, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), json_encode($attribution, JSON_THROW_ON_ERROR), 'campaign-2026-11-21-legal-v2']);
}
function campaign_http(string $url, array $headers, array $payload, int $timeout = 12): array {
    if (defined('CAMPAIGN_TEST_HARNESS') && CAMPAIGN_TEST_HARNESS === true && isset($GLOBALS['campaign_test_http'])) return ($GLOBALS['campaign_test_http'])($url, $headers, $payload);
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR), CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => $timeout, CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json', 'Accept: application/json'], $headers)]);
    $body = curl_exec($curl); $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl);
    return ['status' => $status, 'body' => $body === false ? [] : (json_decode($body, true) ?: [])];
}
function campaign_enrich(PDO $db, array $row, ?callable $http = null): void {
    if ($row['ai_status'] === 'complete' || (int)$row['ai_attempts'] >= 3 || !campaign_config('ANTHROPIC_API_KEY')) return;
    $http = $http ?? 'campaign_http'; $data = json_decode($row['payload'], true);
    $score = campaign_stored_score($data);
    // No name, email, phone, URL, message, attribution or IP is sent to the model.
    $context = array_merge(array_intersect_key($data, array_flip(['sector', 'objective', 'budget', 'urgency', 'has_website'])), $score, ['service' => $row['service']]);
    $db->prepare('UPDATE leads SET ai_attempts=ai_attempts+1 WHERE id=?')->execute([$row['id']]);
    $result = $http('https://api.anthropic.com/v1/messages', ['x-api-key: ' . campaign_config('ANTHROPIC_API_KEY'), 'anthropic-version: 2023-06-01'], [
        'model' => campaign_config('ANTHROPIC_MODEL', 'claude-haiku-5-5'), 'max_tokens' => 350,
        'system' => 'Eres un asistente interno de Soto Digital. La puntuación y categoría son definitivas, calculadas en PHP: no las cambies. Resume únicamente los datos observados, sin inventar resultados, precios, contactos ni garantías. No realices acciones. Devuelve solo JSON con dos strings: resumen (máximo 600 caracteres) y siguiente_paso (máximo 300 caracteres). Redacta en español. La prioridad requiere revisión humana.',
        'messages' => [['role' => 'user', 'content' => json_encode($context, JSON_UNESCAPED_UNICODE)]],
    ]);
    if ($result['status'] < 200 || $result['status'] >= 300) return;
    $text = '';
    foreach (($result['body']['content'] ?? []) as $block) if (($block['type'] ?? '') === 'text') $text .= $block['text'] ?? '';
    $text = preg_replace('/\A\s*```(?:json)?\s*|\s*```\s*\z/', '', $text);
    $parsed = json_decode($text, true);
    if (!is_array($parsed) || !is_string($parsed['resumen'] ?? null) || !is_string($parsed['siguiente_paso'] ?? null) || trim($parsed['resumen']) === '' || trim($parsed['siguiente_paso']) === '') return;
    $db->prepare("UPDATE leads SET ai_status='complete',ai_summary=?,ai_next_step=?,brevo_synced=0 WHERE id=?")->execute([mb_substr($parsed['resumen'], 0, 600), mb_substr($parsed['siguiente_paso'], 0, 300), $row['id']]);
}
function campaign_brevo_sync(PDO $db, array $row, ?callable $http = null): void {
    if ($row['brevo_synced'] || !campaign_config('BREVO_API_KEY') || (int)campaign_config('BREVO_LIST_ID') < 1) return;
    $http = $http ?? 'campaign_http'; $data = json_decode($row['payload'], true); $attr = json_decode($row['attribution'], true);
    // Keep the CRM contact on the newest request while preserving all local lead history.
    $latest = $db->prepare("SELECT id FROM leads WHERE lower(json_extract(payload,'$.email'))=lower(?) ORDER BY created_at DESC,rowid DESC LIMIT 1");
    $latest->execute([$data['email']]);
    if ($latest->fetchColumn() !== $row['id']) {
        $db->prepare('UPDATE leads SET brevo_synced=1 WHERE id=?')->execute([$row['id']]); return;
    }
    $score = campaign_stored_score($data);
    $attributes = [
      'NOMBRE_COMPLETO' => $data['name'], 'MOVIL_CONTACTO' => $data['phone'], 'WEB_NEGOCIO' => $data['website'], 'TIENE_WEB' => $data['website'] !== '',
      'SERVICIO' => $row['service'], 'SECTOR' => $data['sector'] ?? '', 'OBJETIVO' => $data['objective'] ?? '', 'PRESUPUESTO' => $data['budget'] ?? '', 'URGENCIA' => $data['urgency'] ?? '',
      'PUNTUACION' => $score['score'], 'POTENCIAL' => $score['category'], 'VERSION_PUNTUACION' => $score['score_version'],
      'RESUMEN_IA' => $row['ai_summary'], 'SIGUIENTE_PASO_IA' => $row['ai_next_step'], 'ESTADO_IA' => $row['ai_status'],
      'UTM_SOURCE' => $attr['utm_source'] ?? '', 'UTM_MEDIUM' => $attr['utm_medium'] ?? '', 'UTM_CAMPAIGN' => $attr['utm_campaign'] ?? '', 'UTM_CONTENT' => $attr['utm_content'] ?? '', 'UTM_TERM' => $attr['utm_term'] ?? '',
      'GCLID' => $attr['gclid'] ?? '', 'GBRAID' => $attr['gbraid'] ?? '', 'WBRAID' => $attr['wbraid'] ?? '', 'FECHA_SOLICITUD' => substr($row['created_at'], 0, 10), 'ID_SOLICITUD' => $row['id'], 'VERSION_PRIVACIDAD' => $row['consent_version'],
    ];
    $result = $http('https://api.brevo.com/v3/contacts', ['api-key: ' . campaign_config('BREVO_API_KEY')], ['email' => $data['email'], 'attributes' => $attributes, 'listIds' => [(int)campaign_config('BREVO_LIST_ID')], 'updateEnabled' => true]);
    // Do not change marketing blacklist/subscription status, trigger campaigns or subscribe to newsletter lists.
    if ($result['status'] >= 200 && $result['status'] < 300) $db->prepare('UPDATE leads SET brevo_synced=1 WHERE id=?')->execute([$row['id']]);
}
function campaign_uuid(string $seed): string {
    $hex = hash('sha256', $seed);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-4' . substr($hex, 13, 3) . '-a' . substr($hex, 17, 3) . '-' . substr($hex, 20, 12);
}
function campaign_send(array $message, string $key): ?bool {
    if (!campaign_config('BREVO_API_KEY') || !campaign_config('CAMPAIGN_MAIL_FROM')) return false;
    $result = campaign_http('https://api.brevo.com/v3/smtp/email', ['api-key: ' . campaign_config('BREVO_API_KEY')], [
      'sender' => ['email' => campaign_config('CAMPAIGN_MAIL_FROM'), 'name' => 'Soto Digital'],
      'to' => array_map(static function ($email) { return ['email' => $email]; }, $message['to']),
      'replyTo' => ['email' => $message['reply_to']], 'subject' => $message['subject'], 'textContent' => $message['text'], 'headers' => ['idempotencyKey' => campaign_uuid($key)],
    ]);
    if ($result['status'] === 0 || $result['status'] >= 500) return null;
    return $result['status'] >= 200 && $result['status'] < 300;
}
function campaign_deliver(PDO $db, string $id, ?callable $sender = null): void {
    if (!$sender && (!campaign_config('BREVO_API_KEY') || !campaign_config('CAMPAIGN_MAIL_FROM'))) return;
    $sender = $sender ?? 'campaign_send';
    $stmt = $db->prepare('SELECT * FROM leads WHERE id=?'); $stmt->execute([$id]); $row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) return;
    $data = json_decode($row['payload'], true); $score = campaign_stored_score($data);
    $admin = campaign_config('CAMPAIGN_MAIL_TO', 'ads@soto-digital.com');
    $options = campaign_options();
    $text = 'Servicio: ' . $row['service'] . "\nNombre: " . $data['name'] . "\nEmail: " . $data['email'] . "\nMóvil: " . $data['phone'] . "\nWeb: " . ($data['website'] ?: 'No indicada') . "\n";
    foreach (['sector', 'objective', 'budget', 'urgency'] as $key) $text .= $options[$key]['label'] . ': ' . ($options[$key]['options'][$data[$key] ?? ''] ?? 'No indicado') . "\n";
    $text .= 'Puntuación: ' . $score['score'] . '/10 | ' . $score['category'] . ' (' . $score['score_version'] . ")\nResumen IA (revisar): " . ($row['ai_summary'] ?: 'No disponible') . "\nSiguiente paso IA (revisar): " . ($row['ai_next_step'] ?: 'Revisar y contactar') . "\n\n" . $data['message'];
    $messages = [
      'admin_sent' => ['to' => [$admin], 'reply_to' => $data['email'], 'subject' => 'Nueva mini-auditoría | Soto Digital', 'text' => $text],
      'confirmation_sent' => ['to' => [$data['email']], 'reply_to' => $admin, 'subject' => 'Hemos recibido tu solicitud | Soto Digital', 'text' => 'Hola ' . $data['name'] . ",\n\nGracias por contarnos tu proyecto. Hemos recibido tu solicitud de mini-auditoría inicial gratuita. Nos pondremos en contacto contigo lo antes posible por teléfono o email para conocer tu negocio y acordar el alcance de la revisión. Si necesitas ayuda antes, responde a este correo.\n\nEl equipo de Soto Digital\n659 625 857\n" . $admin],
    ];
    foreach ($messages as $column => $message) {
        $state = $column === 'admin_sent' ? 'admin_state' : 'confirmation_state';
        if ($row[$column] || $row[$state] === 'sending') continue;
        // An interrupted send stays flagged for manual review; never blindly resend an ambiguous email.
        $db->prepare('UPDATE leads SET ' . $state . "='sending' WHERE id=?")->execute([$id]);
        $sent = $sender($message, 'campaign-' . $id . '-' . $column);
        if ($sent === true) {
            $db->prepare('UPDATE leads SET ' . $column . '=1,' . $state . "='sent' WHERE id=?")->execute([$id]);
        } elseif ($sent === false) {
            $db->prepare('UPDATE leads SET ' . $state . "='pending' WHERE id=?")->execute([$id]);
        }
    }
}
function campaign_process(PDO $db, string $id, ?callable $http = null, ?callable $sender = null): void {
    $stmt = $db->prepare('SELECT * FROM leads WHERE id=?');
    $stmt->execute([$id]); $row = $stmt->fetch(PDO::FETCH_ASSOC); if (!$row) return;
    try { campaign_enrich($db, $row, $http); } catch (Throwable $e) { error_log('Campaign AI pending'); }
    $stmt->execute([$id]); $row = $stmt->fetch(PDO::FETCH_ASSOC);
    try { campaign_brevo_sync($db, $row, $http); } catch (Throwable $e) { error_log('Campaign CRM pending'); }
    campaign_deliver($db, $id, $sender);
}
