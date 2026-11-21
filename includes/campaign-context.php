<?php
require_once __DIR__ . '/campaign-leads.php';
$escape = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
// Public HTML contains no session state. Tokens are issued by api/token.php on demand.
$error = ''; $old = []; $formCsrf = ''; $formSubmission = ''; $attributionToken = '';
$ready = campaign_ready(); $leadForm = true;
