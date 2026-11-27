<?php
declare(strict_types=1);
/** Entrada HTTPS independiente. POST y token privado obligatorio. */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('Cache-Control: no-store');
header('Content-Type: text/plain; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit; }
try {
    $config = require __DIR__ . '/whatsapp-config.php';
    $expected = $config['worker_token'] ?? null;
    $received = $_SERVER['HTTP_X_SOTO_WORKER_TOKEN'] ?? '';
    if (!is_string($expected) || strlen($expected) < 32 || !is_string($received) || !hash_equals($expected, $received)) {
        http_response_code(403); exit;
    }
    if (($config['brevo_enabled'] ?? false) !== true) {
        http_response_code(503); echo 'BREVO_DISABLED'; exit;
    }
    require_once __DIR__ . '/agent-runtime.php';
    iniciarRuntimeAgente();
    require_once __DIR__ . '/lead-notifier.php';
    $estado = sotoSincronizarUnLead($config);
    http_response_code(200);
    echo strtoupper($estado);
} catch (Throwable $e) {
    error_log('SOTO_BREVO_RUN_ERROR: ' . get_class($e));
    http_response_code(503);
    echo 'BREVO_UNAVAILABLE';
}
