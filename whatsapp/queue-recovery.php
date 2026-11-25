<?php
declare(strict_types=1);

require_once __DIR__ . '/webhook-event-processor.php';

/**
 * Diagnóstico de la cola de Soto Digital.
 *
 * SOLO LECTURA:
 * - No reintenta eventos.
 * - No cambia estados.
 * - No envía WhatsApps.
 * - No muestra teléfonos ni textos de clientes.
 */

const SOTO_QUEUE_STALE_SECONDS = 180;

function diagnosticarCola(): array
{
    $dir = sotoQueueDirectory();

    $resultado = [
        'total' => 0,
        'pendientes' => 0,
        'completados' => 0,
        'revision' => 0,
        'interrumpidos' => 0,
        'procesando' => 0,
        'corruptos' => 0,
        'inconsistentes' => 0,
        'detalles' => []
    ];

    $archivos = glob($dir . '/*.json');

    if ($archivos === false) {
        throw new RuntimeException(
            'No se pudo examinar la cola'
        );
    }

    foreach ($archivos as $archivo) {
        $resultado['total']++;

        // Identificador de diagnóstico, sin datos
        // personales ni contenido del mensaje.
        $referencia = substr(
            basename($archivo, '.json'),
            0,
            12
        );

        $raw = @file_get_contents($archivo);

        if ($raw === false) {
            $resultado['corruptos']++;
            $resultado['detalles'][] = [
                'ref' => $referencia,
                'estado' => 'corrupto',
                'motivo' => 'No se pudo leer'
            ];
            continue;
        }

        try {
            $registro = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            $registro = null;
        }

        if (!is_array($registro)) {
            $resultado['corruptos']++;
            $resultado['detalles'][] = [
                'ref' => $referencia,
                'estado' => 'corrupto',
                'motivo' => 'JSON no válido'
            ];
            continue;
        }

        $estado = $registro['estado'] ?? null;
        $messageId = $registro['message_id'] ?? null;
        $telefono = $registro['telefono'] ?? null;

        if (
            !is_string($estado) ||
            !is_string($messageId) ||
            !str_starts_with($messageId, 'wamid.') ||
            !is_string($telefono) ||
            !preg_match('/^[0-9]{8,16}$/', $telefono) ||
            basename($archivo) !==
                hash('sha256', $messageId) . '.json'
        ) {
            $resultado['corruptos']++;
            $resultado['detalles'][] = [
                'ref' => $referencia,
                'estado' => 'corrupto',
                'motivo' => 'Registro inconsistente'
            ];
            continue;
        }

        $edad = time() - (int) filemtime($archivo);

        if ($estado === 'pendiente') {
            $resultado['pendientes']++;
            continue;
        }

        if ($estado === 'completado') {
            $resultado['completados']++;
            continue;
        }

        if ($estado === 'revision') {
            $resultado['revision']++;
            $resultado['detalles'][] = [
                'ref' => $referencia,
                'estado' => 'revision',
                'motivo' =>
                    'Requiere revisión antes de reintentar'
            ];
            continue;
        }

        if ($estado === 'procesando') {
            if ($edad >= SOTO_QUEUE_STALE_SECONDS) {
                $resultado['interrumpidos']++;
                $resultado['detalles'][] = [
                    'ref' => $referencia,
                    'estado' => 'interrumpido',
                    'motivo' =>
                        'Sin actualización durante al ' .
                        'menos 180 segundos'
                ];
            } else {
                $resultado['procesando']++;
            }

            continue;
        }

        $resultado['inconsistentes']++;
        $resultado['detalles'][] = [
            'ref' => $referencia,
            'estado' => 'inconsistente',
            'motivo' => 'Estado desconocido'
        ];
    }

    return $resultado;
}

function mostrarDiagnosticoCola(array $resultado): void
{
    echo "\n=== DIAGNÓSTICO DE COLA SOTO DIGITAL ===\n\n";

    foreach ([
        'total' => 'Total de mensajes',
        'pendientes' => 'Pendientes',
        'completados' => 'Completados',
        'procesando' => 'En proceso',
        'revision' => 'Requieren revisión',
        'interrumpidos' => 'Posibles interrupciones',
        'corruptos' => 'Registros corruptos',
        'inconsistentes' => 'Estados desconocidos'
    ] as $clave => $titulo) {
        echo $titulo . ': ' .
            $resultado[$clave] . "\n";
    }

    if ($resultado['detalles']) {
        echo "\n--- INCIDENCIAS ---\n";

        foreach ($resultado['detalles'] as $item) {
            echo '[' . $item['ref'] . '] ' .
                $item['estado'] . ': ' .
                $item['motivo'] . "\n";
        }
    }

    echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
}
