<?php
declare(strict_types=1);

/**
 * Coordinador de entrega - Soto Digital.
 *
 * Integra el diario de envíos y WhatsApp Bridge.
 * No procesa directamente la cola de entrada.
 */

require_once __DIR__ . '/delivery-journal.php';
require_once __DIR__ . '/whatsapp-bridge.php';

/**
 * Envía una respuesta y registra su resultado.
 *
 * $incomingId: ID del mensaje recibido de Meta.
 * $telefono: destinatario de la respuesta.
 * $resultadoControlador: salida de responderAlCliente().
 * $transportador: envío simulado o real.
 */
function procesarEntregaConDiario(
    string $incomingId,
    string $telefono,
    array $resultadoControlador,
    callable $transportador
): array {
    $respuesta = $resultadoControlador['respuesta'] ?? null;

    if (!is_string($respuesta) || trim($respuesta) === '') {
        return [
            'estado' => 'sin_mensaje',
            'aceptado' => false
        ];
    }

    // Impedir registrar y enviar respuestas vacías,
    // con destinatarios incorrectos o demasiado largas.
    if (
        !preg_match('/^[0-9]{8,16}$/', $telefono) ||
        strlen($respuesta) > 4000
    ) {
        throw new InvalidArgumentException(
            'Datos de envío no válidos'
        );
    }

    // 1. Preparar el registro de envío.
    // Si este ID ya tiene otra respuesta, se rechazará.
    sotoPrepararEnvio(
        $incomingId,
        $respuesta
    );

    // 2. Registrar ANTES de llamar a Meta.
    // Si ya estaba enviando, aceptado, rechazado
    // o incierto, esta función impedirá repetirlo.
    sotoIniciarEnvio($incomingId);

    try {
        // 3. Efectuar envío mediante el Bridge.
        $envio = ejecutarWhatsAppBridge(
            $telefono,
            $resultadoControlador,
            $transportador
        );

    } catch (Throwable $e) {
        // Una excepción puede ocurrir después de
        // que Meta haya recibido la solicitud.
        // Por seguridad, NO reintentamos.
        sotoCerrarEnvio(
            $incomingId,
            'incierto'
        );

        return [
            'estado' => 'incierto',
            'aceptado' => false,
            'requiere_revision' => true
        ];
    }

    $estadoBridge = $envio['estado'] ?? '';

    // 4. Meta aceptó el mensaje.
    if (in_array(
        $estadoBridge,
        ['aceptado', 'aceptado_sin_vincular'],
        true
    )) {
        $metaId = $envio['message_id'] ?? null;

        if (!is_string($metaId) ||
            !str_starts_with($metaId, 'wamid.')) {

            sotoCerrarEnvio(
                $incomingId,
                'incierto'
            );

            return [
                'estado' => 'incierto',
                'aceptado' => false,
                'requiere_revision' => true
            ];
        }

        sotoCerrarEnvio(
            $incomingId,
            'aceptado',
            $metaId
        );

        // Meta aceptó el mensaje, pero el resumen
        // de propuesta no pudo vincularse.
        if ($estadoBridge === 'aceptado_sin_vincular') {
            return [
                'estado' => 'revision_vinculacion',
                'aceptado' => true,
                'message_id' => $metaId,
                'requiere_revision' => true
            ];
        }

        return [
            'estado' => 'aceptado',
            'aceptado' => true,
            'message_id' => $metaId,
            'requiere_revision' => false
        ];
    }

    // 5. Meta devolvió un error HTTP.
    if ($estadoBridge === 'envio_fallido') {
        $http = (int) ($envio['http_status'] ?? 0);

        // Rechazo explícito de la solicitud.
        // Incluimos 429: requiere revisar límites.
        if ($http >= 400 && $http < 500) {
            sotoCerrarEnvio(
                $incomingId,
                'rechazado'
            );

            return [
                'estado' => 'rechazado',
                'aceptado' => false,
                'http_status' => $http,
                'requiere_revision' => true
            ];
        }
    }

    // HTTP 5xx, respuesta incompleta u otro
    // resultado cuyo efecto desconocemos.
    sotoCerrarEnvio(
        $incomingId,
        'incierto'
    );

    return [
        'estado' => 'incierto',
        'aceptado' => false,
        'requiere_revision' => true
    ];
}
