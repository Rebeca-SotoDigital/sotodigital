<?php
declare(strict_types=1);

/**
 * Soto Digital - Nuevo webhook del agente comercial.
 *
 * Verifica los eventos de Meta y guarda los mensajes
 * en la cola privada.
 *
 * NO ejecuta Claude directamente.
 * NO envía mensajes desde el webhook.
 * NO sustituye el webhook de producción actual.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function configurarColaAgente(array $config): void
{
    $base = $config['agent_storage_dir'] ?? '';

    if (
        !is_string($base) ||
        $base === '' ||
        !str_starts_with($base, '/')
    ) {
        throw new RuntimeException(
            'Falta una ruta absoluta de almacenamiento privado'
        );
    }

    $directorioPublico = realpath(
        (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')
    );

    $directorioCodigo = realpath(__DIR__);
    $directorioPrivado = realpath($base);

    if ($directorioPrivado === false) {
        throw new RuntimeException(
            'El directorio privado no existe'
        );
    }

    // Evitar guardar conversaciones y leads
    // dentro de una carpeta pública.
    foreach (
        [$directorioPublico, $directorioCodigo] as $publico
    ) {
        if (
            is_string($publico) &&
            $publico !== '' &&
            (
                $directorioPrivado === $publico ||
                str_starts_with(
                    $directorioPrivado,
                    rtrim($publico, '/') . '/'
                )
            )
        ) {
            throw new RuntimeException(
                'El almacenamiento no puede ser público'
            );
        }
    }

    $cola = $directorioPrivado . '/queue';

    if (!is_dir($cola)) {
        $oldUmask = umask(0077);

        try {
            if (
                !mkdir($cola, 0700, true) &&
                !is_dir($cola)
            ) {
                throw new RuntimeException(
                    'No se pudo crear la cola'
                );
            }
        } finally {
            umask($oldUmask);
        }
    }

    if (!is_writable($cola)) {
        throw new RuntimeException(
            'La cola no permite escritura'
        );
    }

    putenv('SOTO_QUEUE_DIR=' . $cola);
}

try {
    $config = require __DIR__ . '/whatsapp-config.php';

    if (!is_array($config)) {
        throw new RuntimeException(
            'Configuración de WhatsApp inválida'
        );
    }

    $verifyToken = $config['verify_token'] ?? '';
    $appSecret = $config['app_secret'] ?? '';

    if (
        !is_string($verifyToken) ||
        $verifyToken === '' ||
        !is_string($appSecret) ||
        $appSecret === ''
    ) {
        throw new RuntimeException(
            'Faltan credenciales de verificación'
        );
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? '';

    // 1. Meta comprueba el webhook mediante GET.
    if ($method === 'GET') {
        $mode = $_GET['hub_mode']
            ?? $_GET['hub.mode']
            ?? '';

        $token = $_GET['hub_verify_token']
            ?? $_GET['hub.verify_token']
            ?? '';

        $challenge = $_GET['hub_challenge']
            ?? $_GET['hub.challenge']
            ?? '';

        if (
            $mode === 'subscribe' &&
            is_string($token) &&
            hash_equals($verifyToken, $token) &&
            is_string($challenge)
        ) {
            header(
                'Content-Type: text/plain; charset=utf-8'
            );

            http_response_code(200);
            echo $challenge;
            exit;
        }

        http_response_code(403);
        exit;
    }

    if ($method !== 'POST') {
        http_response_code(405);
        exit;
    }

    // Desactivado hasta que se autorice
    // el despliegue de producción.
    if (($config['agent_enabled'] ?? false) !== true) {
        http_response_code(503);
        exit;
    }

    // 2. Leer el cuerpo original de Meta.
    $raw = file_get_contents('php://input');

    if (
        !is_string($raw) ||
        strlen($raw) > 262144
    ) {
        http_response_code(400);
        exit;
    }

    // 3. Verificar la firma digital.
    $signature =
        $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

    $expected = 'sha256=' . hash_hmac(
        'sha256',
        $raw,
        $appSecret
    );

    if (
        !is_string($signature) ||
        !hash_equals($expected, $signature)
    ) {
        http_response_code(403);
        exit;
    }

    // 4. Interpretar el evento.
    $payload = json_decode(
        $raw,
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($payload)) {
        http_response_code(400);
        exit;
    }

    // 5. Preparar almacenamiento privado.
    configurarColaAgente($config);

    // 6. Guardar mensajes válidos, sin consultar Claude.
    require_once __DIR__ .
        '/webhook-event-processor.php';

    $resultado = procesarEventoWebhook($payload);

    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(200);

    echo 'EVENT_RECEIVED';

} catch (JsonException $e) {
    http_response_code(400);

} catch (Throwable $e) {
    // Meta podrá volver a enviar el evento
    // si falla su almacenamiento.
    // No exponer claves ni datos personales.
    error_log(
        'SOTO_AGENT_WEBHOOK_ERROR: ' .
        get_class($e)
    );

    http_response_code(503);
}
