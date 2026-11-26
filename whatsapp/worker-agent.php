<?php
declare(strict_types=1);

/**
 * Ejecutor HTTPS protegido - Soto Digital.
 *
 * Solo acepta POST autenticado.
 * Procesa como máximo un mensaje por invocación.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Cache-Control: no-store');
header('Content-Type: text/plain; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

try {
    // Comprobar autorización antes de cargar
    // Claude o procesar conversaciones.
    $config = require __DIR__ . '/whatsapp-config.php';

    if (!is_array($config)) {
        throw new RuntimeException(
            'Configuración inválida'
        );
    }

    $tokenEsperado = $config['worker_token'] ?? null;

    $tokenRecibido =
        $_SERVER['HTTP_X_SOTO_WORKER_TOKEN'] ?? '';

    if (
        !is_string($tokenEsperado) ||
        strlen($tokenEsperado) < 32 ||
        !is_string($tokenRecibido) ||
        !hash_equals($tokenEsperado, $tokenRecibido)
    ) {
        http_response_code(403);
        exit;
    }

    require_once __DIR__ . '/agent-runtime.php';

    $config = iniciarRuntimeAgente();

    if (($config['agent_live_send'] ?? false) !== true) {
        http_response_code(503);
        exit;
    }

    require_once __DIR__ . '/queue-worker.php';

    $resultado = procesarColaWhatsApp(
        'enviarWhatsAppMeta',
        1
    );

    // Sin nombres, teléfonos ni mensajes
    // en la respuesta HTTP.
    http_response_code(200);

    echo 'OK: ' .
        (int) $resultado['completados'] .
        ' completados; ' .
        (int) $resultado['revision'] .
        ' en revisión';

} catch (Throwable $e) {
    // No publicar detalles internos ni credenciales.
    error_log(
        'SOTO_AGENT_WORKER_ERROR: ' .
        get_class($e)
    );

    http_response_code(503);
    echo 'WORKER_UNAVAILABLE';
}
