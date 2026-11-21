<?php
require_once __DIR__ . '/../includes/campaign-leads.php';
campaign_session();
header('X-Robots-Tag: noindex, nofollow');
$json = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
function lead_response(bool $ok, string $message, int $status = 200, ?string $receipt = null): void {
    global $json, $return;
    if ($json) {
        http_response_code($status); header('Content-Type: application/json; charset=UTF-8');
        $body = ['ok' => $ok];
        if (!$ok) $body['error'] = $message;
        if ($ok) $body['next'] = 'campanas/gracias.php';
        echo json_encode($body); exit;
    }
    if ($ok) { header('Location: ../campanas/gracias.php', true, 303); exit; }
    $_SESSION['campaign_error'] = $message;
    header('Location: ../campanas/' . ($return ?? 'index.php') . '#propuesta', true, 303); exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Allow: POST'); http_response_code(405); exit; }
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 16000) lead_response(false, 'La solicitud es demasiado larga.', 413);
$services = require __DIR__ . '/../includes/campaign-services.php';
$slug = is_string($_POST['service'] ?? null) ? $_POST['service'] : '';
if (!isset($services[$slug])) lead_response(false, 'Servicio no válido.', 400);
$return = $slug === 'estrategia-digital' ? 'index.php' : $slug . '.php';
if (!is_string($_POST['csrf'] ?? null) || empty($_SESSION['campaign_csrf']) || !hash_equals($_SESSION['campaign_csrf'], $_POST['csrf'])) lead_response(false, 'La sesión ha caducado. Recarga la página y vuelve a intentarlo.', 403);
$submission = $_POST['submission'] ?? '';
if (!is_string($submission)) lead_response(false, 'Solicitud no válida.', 400);
if (isset($_SESSION['campaign_received']) && hash_equals($_SESSION['campaign_received'], $submission)) lead_response(true, '', 200, $submission);
if (empty($_SESSION['campaign_submission']) || !hash_equals($_SESSION['campaign_submission'], $submission)) lead_response(false, 'Esta solicitud ha caducado. Recarga la página.', 409);
if (!campaign_ready()) lead_response(false, 'La solicitud online todavía no está disponible. Vuelve a intentarlo más adelante.', 503);
try {
    $db = campaign_db();
    // REMOTE_ADDR only: do not trust visitor-controlled X-Forwarded-For headers.
    if (!campaign_rate_allow($db, $_SERVER['REMOTE_ADDR'] ?? 'unknown')) { header('Retry-After: 3600'); lead_response(false, 'Has alcanzado el límite de solicitudes. Inténtalo más tarde o vuelve a intentarlo más tarde.', 429); }
} catch (Throwable $e) { error_log('Campaign storage or rate limiter unavailable'); lead_response(false, 'No podemos guardar tu solicitud ahora. Inténtalo más tarde o vuelve a intentarlo más tarde.', 503); }
$screened = !empty($_POST['company_fax']) || campaign_screened($_POST);
if (!$screened) {
    try { $data = campaign_validate($_POST); } catch (InvalidArgumentException $e) { lead_response(false, $e->getMessage(), 422); }
}
$token = $_POST['cf-turnstile-response'] ?? '';
if (!is_string($token) || strlen($token) > 2048 || !$token) lead_response(false, 'Completa la verificación antispam.', 422);
$curl = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query(['secret' => campaign_config('TURNSTILE_SECRET_KEY'), 'response' => $token]), CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 10]);
$response = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl);
$verification = $response ? json_decode($response, true) : null;
$hosts = array_filter(array_map('trim', explode(',', campaign_config('CAMPAIGN_ALLOWED_HOSTS', 'soto-digital.com,www.soto-digital.com'))));
if ($status !== 200 || empty($verification['success']) || ($verification['action'] ?? '') !== 'proposal' || !in_array($verification['hostname'] ?? '', $hosts, true)) lead_response(false, 'No hemos podido verificar la solicitud. Repite la verificación antispam.', 422);
if ($screened) {
    // Same public success shape, no contact, receipt/conversion, message or personal data persisted.
    $_SESSION['campaign_screened'] = true;
    unset($_SESSION['campaign_received'], $_SESSION['campaign_conversion'], $_SESSION['campaign_submission']);
    lead_response(true, '');
}
$attributionToken = is_string($_POST['attribution_token'] ?? null) ? $_POST['attribution_token'] : '';
$attribution = $_SESSION['campaign_attribution'][$attributionToken] ?? [];
try { campaign_store($db, $submission, $slug, $data, $attribution); }
catch (Throwable $e) { error_log('Campaign lead storage failed'); lead_response(false, 'No hemos podido guardar tu solicitud. Inténtalo de nuevo o vuelve a intentarlo más tarde.', 503); }
$_SESSION['campaign_received'] = $submission;
$_SESSION['campaign_conversion'] = ['id' => $submission, 'service' => $slug];
unset($_SESSION['campaign_screened'], $_SESSION['campaign_old'], $_SESSION['campaign_submission'], $_SESSION['campaign_attribution'][$attributionToken]);
// Slow external calls run in the private CLI worker. A valid lead is already durable.
lead_response(true, '', 200, $submission);
