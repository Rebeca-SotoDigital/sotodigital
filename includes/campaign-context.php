<?php
require_once __DIR__ . '/campaign-leads.php';
campaign_session();
$escape = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
if (empty($_SESSION['campaign_csrf'])) $_SESSION['campaign_csrf'] = bin2hex(random_bytes(32));
if (empty($_SESSION['campaign_submission'])) $_SESSION['campaign_submission'] = bin2hex(random_bytes(16));
$attribution = [];
foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid', 'gbraid', 'wbraid'] as $key) {
    if (isset($_GET[$key]) && is_string($_GET[$key])) $attribution[$key] = substr(preg_replace('/[^a-zA-Z0-9_ .+\-]/', '', $_GET[$key]), 0, 250);
}
if ($attribution) $_SESSION['campaign_last_attribution'] = ['data' => $attribution, 'time' => time()];
elseif (time() - ($_SESSION['campaign_last_attribution']['time'] ?? 0) < 1800) $attribution = $_SESSION['campaign_last_attribution']['data'];
$attributionToken = bin2hex(random_bytes(12));
$_SESSION['campaign_attribution'][$attributionToken] = $attribution;
if (count($_SESSION['campaign_attribution']) > 10) array_shift($_SESSION['campaign_attribution']);
$error = $_SESSION['campaign_error'] ?? ''; unset($_SESSION['campaign_error']);
$old = $_SESSION['campaign_old'] ?? []; unset($_SESSION['campaign_old']);
$ready = campaign_ready();
$leadForm = true;
