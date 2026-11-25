<?php
declare(strict_types=1);

require_once __DIR__ . '/claude-client.php';
require_once __DIR__ . '/conversation-store.php';
require_once __DIR__ . '/lead-confirmation.php';

/**
 * Controlador de conversaciones de Soto Digital.
 *
 * Devuelve una respuesta para el cliente.
 * NO envía mensajes de WhatsApp.
 */

function responderAlCliente(
    string $phone,
    string $texto
): array {
    $texto = trim($texto);

    if ($texto === '') {
        return [
            'estado' => 'vacio',
            'respuesta' => '',
            'requiere_marcar_envio' => false
        ];
    }

    if (mb_strlen($texto) > 2000) {
        return [
            'estado' => 'demasiado_largo',
            'respuesta' =>
                '¿Puedes enviarme un mensaje más corto?',
            'requiere_marcar_envio' => false
        ];
    }

    // Recuperar la conversación anterior.
    $historial = cargarConversacion($phone);

    // Consultar el estado del pipeline.
    $fase = pipelineEstado(
        $phone,
        fn(array $state) => [
            'guardar' => false,
            'resultado' => $state['fase'] ?? ''
        ]
    );

    $comando = mb_strtoupper($texto);
    $requiereMarcarEnvio = false;

    // 1. Solicitar una propuesta comercial.
    if ($comando === 'SOLICITAR PROPUESTA') {

        $historialActual = array_merge(
            $historial,
            [
                [
                    'role' => 'user',
                    'content' => $texto
                ]
            ]
        );

        $resultado = prepararConfirmacionLead(
            $phone,
            $historialActual
        );

        $estado = $resultado['estado'];
        $respuesta = $resultado['mensaje'] ?? '';

        if ($estado === 'incompleto') {
            $faltan = $resultado['faltan'] ?? [];

            $respuesta =
                'Para preparar tu propuesta necesitamos ' .
                'completar estos datos: ' .
                implode(', ', $faltan) . '.';
        }

        $requiereMarcarEnvio =
            $estado === 'pendiente_confirmacion';

    // 2. Procesar confirmación, cancelación o corrección.
    } elseif (
        in_array($comando, [
            'CONFIRMAR PROPUESTA',
            'CANCELAR PROPUESTA'
        ], true) ||
        preg_match(
            '/^CORREGIR (NOMBRE|NEGOCIO|EMAIL|NECESIDAD):/ui',
            $texto
        )
    ) {

        $resultado = procesarConfirmacionLead(
            $phone,
            $texto
        );

        $estado = $resultado['estado'];
        $respuesta = $resultado['mensaje'] ??
            'No se ha podido completar la operación.';

        $requiereMarcarEnvio =
            $estado === 'corregido';

    // 3. Si hay un borrador pendiente, no ignorarlo.
    } elseif ($fase === 'pendiente_confirmacion') {

        $estado = 'esperando_confirmacion';
        $respuesta =
            "Tienes una propuesta pendiente de confirmar.\n\n" .
            "Puedes responder:\n" .
            "CONFIRMAR PROPUESTA\n" .
            "CANCELAR PROPUESTA\n\n" .
            "O corregir un dato, por ejemplo:\n" .
            "CORREGIR EMAIL: nuevo@ejemplo.com";

    // 4. Conversación normal con Claude.
    } else {

        $messages = array_merge(
            $historial,
            [
                [
                    'role' => 'user',
                    'content' => $texto
                ]
            ]
        );

        $respuesta = consultarClaude($messages);
        $estado = 'respuesta_claude';
    }

    // Guardar únicamente después de obtener respuesta.
    if ($respuesta !== '') {
        guardarTurno($phone, 'user', $texto);
        guardarTurno($phone, 'assistant', $respuesta);
    }

    return [
        'estado' => $estado,
        'respuesta' => $respuesta,
        'requiere_marcar_envio' =>
            $requiereMarcarEnvio
    ];
}
