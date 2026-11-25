<?php
declare(strict_types=1);

/**
 * Planificador de conversaciones - Soto Digital.
 *
 * Selecciona un único mensaje pendiente
 * por cliente en cada tanda.
 *
 * No modifica la cola, no llama a Claude
 * y no envía WhatsApps.
 */

require_once __DIR__ . '/queue-storage.php';

function planificarColaWhatsApp(): array
{
    $archivos = glob(sotoQueueDirectory() . '/*.json');

    if ($archivos === false) {
        throw new RuntimeException(
            'No se pudo leer la cola'
        );
    }

    $porCliente = [];

    foreach ($archivos as $archivo) {
        $raw = file_get_contents($archivo);

        if ($raw === false) {
            throw new RuntimeException(
                'No se pudo leer un mensaje'
            );
        }

        try {
            $registro = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'Existe un registro corrupto'
            );
        }

        if (!is_array($registro)) {
            throw new RuntimeException(
                'Registro de cola inválido'
            );
        }

        $telefono = $registro['telefono'] ?? null;
        $id = $registro['message_id'] ?? null;
        $estado = $registro['estado'] ?? null;

        if (
            !is_string($telefono) ||
            !preg_match('/^[0-9]{8,16}$/', $telefono) ||
            !is_string($id) ||
            !str_starts_with($id, 'wamid.') ||
            basename($archivo) !==
                hash('sha256', $id) . '.json' ||
            !in_array(
                $estado,
                [
                    'pendiente',
                    'procesando',
                    'revision',
                    'completado'
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Existe un registro inconsistente'
            );
        }

        if ($estado === 'completado') {
            continue;
        }

        // Fecha enviada por Meta, si es válida.
        $timestamp = $registro['timestamp_meta'] ?? null;

        if (
            !is_string($timestamp) ||
            !ctype_digit($timestamp)
        ) {
            $timestamp = null;
        }

        // Como alternativa, usamos la fecha
        // de recepción registrada por PHP.
        $recibido = strtotime(
            (string) ($registro['recibido_en'] ?? '')
        );

        $orden = $timestamp !== null
            ? (int) $timestamp
            : ($recibido ?: 0);

        $porCliente[$telefono][] = [
            'archivo' => $archivo,
            'id' => $id,
            'estado' => $estado,
            'orden' => $orden
        ];
    }

    $elegibles = [];
    $bloqueados = [];

    foreach ($porCliente as $mensajes) {
        usort(
            $mensajes,
            function (array $a, array $b): int {
                $comparacion =
                    $a['orden'] <=> $b['orden'];

                if ($comparacion !== 0) {
                    return $comparacion;
                }

                // Desempate determinista, no
                // necesariamente cronológico.
                return strcmp($a['id'], $b['id']);
            }
        );

        $primero = true;

        foreach ($mensajes as $mensaje) {
            if (
                $primero &&
                $mensaje['estado'] === 'pendiente'
            ) {
                $elegibles[] = $mensaje['archivo'];
            } else {
                $bloqueados[] = [
                    'id_hash' => substr(
                        hash('sha256', $mensaje['id']),
                        0,
                        12
                    ),
                    'estado' => $mensaje['estado']
                ];
            }

            $primero = false;
        }
    }

    return [
        'elegibles' => $elegibles,
        'bloqueados' => $bloqueados,
        'total_elegibles' => count($elegibles),
        'total_bloqueados' => count($bloqueados)
    ];
}
