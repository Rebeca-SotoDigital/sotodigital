<?php
declare(strict_types=1);

require_once __DIR__ . '/webhook-event-processor.php';
require_once __DIR__ . '/message-guard.php';
require_once __DIR__ . '/conversation-controller.php';
require_once __DIR__ . '/whatsapp-bridge.php';
require_once __DIR__ . '/delivery-worker.php';
require_once __DIR__ . '/queue-order.php';

/**
 * Trabajador manual de cola - Soto Digital.
 *
 * Usa el transportador recibido como argumento.
 * No activa el envío real automáticamente.
 */

function guardarEstadoCola(
    string $archivo,
    array $registro
): void {
    $json = json_encode(
        $registro,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    $temporal = tempnam(
        dirname($archivo),
        '.queue-'
    );

    if ($temporal === false) {
        throw new RuntimeException(
            'No se pudo crear archivo temporal'
        );
    }

    try {
        chmod($temporal, 0600);

        if (file_put_contents($temporal, $json) === false) {
            throw new RuntimeException(
                'No se pudo escribir el estado'
            );
        }

        if (!rename($temporal, $archivo)) {
            throw new RuntimeException(
                'No se pudo actualizar la cola'
            );
        }
    } finally {
        if (is_file($temporal)) {
            unlink($temporal);
        }
    }
}


function procesarColaWhatsApp(
    callable $transportador,
    int $limite = 10,
    ?callable $generadorRespuesta = null
): array {
    $generadorRespuesta ??= 'responderAlCliente';

    $directorio = sotoQueueDirectory();

    // Evitar dos trabajadores simultáneos.
    $lock = fopen(
        $directorio . '/worker.lock',
        'c'
    );

    if ($lock === false) {
        throw new RuntimeException(
            'No se pudo abrir el bloqueo'
        );
    }

    $resumen = [
        'completados' => 0,
        'revision' => 0,
        'omitidos' => 0
    ];

    try {
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException(
                'Ya existe un trabajador en ejecución'
            );
        }

        
        // Seleccionar únicamente el primer mensaje
        // no resuelto de cada cliente.
        $plan = planificarColaWhatsApp();

        $archivos = $plan['elegibles'];

        // Orden determinista de la tanda.
        // El orden por cliente ya lo ha decidido
        // el planificador.
        sort($archivos, SORT_STRING);


        $procesados = 0;

        foreach ($archivos as $archivo) {
            if ($procesados >= $limite) {
                break;
            }

            $raw = file_get_contents($archivo);
            $registro = json_decode($raw ?: '', true);

            if (!is_array($registro)) {
                // No borrar ni reintentar un archivo
                // corrupto sin revisión.
                $resumen['revision']++;
                continue;
            }

            if (($registro['estado'] ?? '') !== 'pendiente') {
                $resumen['omitidos']++;
                continue;
            }

            $telefono = $registro['telefono'] ?? '';
            $messageId = $registro['message_id'] ?? '';
            $texto = $registro['texto'] ?? '';

            $registro['estado'] = 'procesando';
            $registro['intentos'] =
                (int) ($registro['intentos'] ?? 0) + 1;

            guardarEstadoCola($archivo, $registro);
            $procesados++;

            try {

$resultado = procesarMensajeProtegido(
    $telefono,
    $messageId,
    function () use (
        $telefono,
        $messageId,
        $texto,
        $transportador,
        $generadorRespuesta
    ): array {
        // 1. Obtener la respuesta comercial.
        $respuesta = $generadorRespuesta(
            $telefono,
            $texto
        );

        if (!is_array($respuesta)) {
            throw new RuntimeException(
                'Respuesta del controlador inválida'
            );
        }

        // 2. Registrar etapas y efectuar el envío.
        $entrega = procesarEntregaConDiario(
            $messageId,
            $telefono,
            $respuesta,
            $transportador
        );

        // 3. Solo completar si Meta ha aceptado
        // y no quedan incidencias de vinculación.
        if (
            ($entrega['estado'] ?? '') !== 'aceptado'
        ) {
            throw new RuntimeException(
                'Entrega pendiente de revisión: ' .
                ($entrega['estado'] ?? 'desconocido')
            );
        }

        return ['ok' => true];
    }
);

                $correcto =
                    ($resultado['estado'] ?? '') ===
                    'completado';

                
$registro['estado'] = $correcto
    ? 'completado'
    : 'revision';

if ($correcto) {
    $resumen['completados']++;
} else {
    $resumen['revision']++;
}


            } catch (Throwable $e) {
                $registro['estado'] = 'revision';
                $resumen['revision']++;
            }

            $registro['actualizado_en'] = gmdate('c');

            guardarEstadoCola($archivo, $registro);
        }

        return $resumen;

    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
