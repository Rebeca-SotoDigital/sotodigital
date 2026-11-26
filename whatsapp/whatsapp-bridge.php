<?php
declare(strict_types=1);

require_once __DIR__ . '/lead-confirmation.php';

/**
 * Puente de envío de WhatsApp - Soto Digital.
 *
 * Acepta un transportador sustituible para pruebas.
 * No recibe ni procesa webhooks entrantes.
 */

function enviarWhatsAppMeta(
    string $telefono,
    string $texto
): array {
    // Protección: envíos reales desactivados por defecto.
    if (getenv('SOTO_WHATSAPP_LIVE_SEND') !== '1') {
        throw new RuntimeException(
            'Envío real desactivado'
        );
    }

    $token = getenv('WHATSAPP_ACCESS_TOKEN');
    $phoneId = getenv('WHATSAPP_PHONE_NUMBER_ID');

    if (!$token || !$phoneId) {
        throw new RuntimeException(
            'Falta configuración privada de Meta'
        );
    }

    if (!preg_match('/^[0-9]+$/', $phoneId)) {
        throw new RuntimeException(
            'Phone Number ID no válido'
        );
    }

    $payload = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $telefono,
        'type' => 'text',
        'text' => [
            'preview_url' => false,
            'body' => $texto
        ]
    ];

    $url = 'https://graph.facebook.com/v27.0/' .
        $phoneId . '/messages';

    $ch = curl_init($url);

    if ($ch === false) {
        throw new RuntimeException(
            'No se pudo iniciar cURL'
        );
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20
    ]);

    $body = curl_exec($ch);
    $status = (int) curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );
    $error = curl_error($ch);

    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException(
            'Fallo de conexión con Meta: ' . $error
        );
    }

    return [
        'http_status' => $status,
        'body' => $body
    ];
}

/**
 * Envía la respuesta del controlador.
 *
 * $transportador puede ser una simulación
 * o la función real enviarWhatsAppMeta().
 */
function ejecutarWhatsAppBridge(
    string $telefono,
    array $resultadoControlador,
    callable $transportador
): array {
    if (!preg_match('/^[0-9]{8,16}$/', $telefono)) {
        throw new InvalidArgumentException(
            'Destinatario no válido'
        );
    }

    $texto = $resultadoControlador['respuesta'] ?? '';

    if (!is_string($texto) || trim($texto) === '') {
        return [
            'estado' => 'sin_mensaje',
            'aceptado' => false
        ];
    }

    if (strlen($texto) > 4000) {
        throw new InvalidArgumentException(
            'Respuesta demasiado larga para el puente'
        );
    }

    // En las pruebas se inyecta un simulador.
    $envio = $transportador($telefono, $texto);

    if (
        !is_array($envio) ||
        !is_int($envio['http_status'] ?? null) ||
        !is_string($envio['body'] ?? null)
    ) {
        throw new RuntimeException(
            'Respuesta del transportador no válida'
        );
    }

    $http = $envio['http_status'];
    $datos = json_decode($envio['body'], true);

    $messageId = is_array($datos)
        ? ($datos['messages'][0]['id'] ?? null)
        : null;

    $aceptado =
        $http >= 200 &&
        $http < 300 &&
        is_string($messageId) &&
        str_starts_with($messageId, 'wamid.');

    if (!$aceptado) {
        return [
            'estado' => 'envio_fallido',
            'aceptado' => false,
            'http_status' => $http
        ];
    }

    $requiereMarcar =
        ($resultadoControlador['requiere_marcar_envio']
            ?? false) === true;

    $marcado = false;

    if ($requiereMarcar) {
        // Vincula exactamente el resumen enviado
        // al borrador de este cliente.
        $marcado = marcarResumenAceptado(
            $telefono,
            $texto,
            true
        );

        if (!$marcado) {
            return [
                'estado' => 'aceptado_sin_vincular',
                'aceptado' => true,
                'message_id' => $messageId,
                'resumen_marcado' => false
            ];
        }
    }

    return [
        'estado' => 'aceptado',
        'aceptado' => true,
        'message_id' => $messageId,
        'resumen_marcado' => $marcado
    ];
}
