<?php
declare(strict_types=1);

/**
 * Revisión supervisada - Soto Digital.
 *
 * No modifica mensajes.
 * No realiza reintentos.
 * No expone teléfonos ni conversaciones.
 */

require_once __DIR__ . '/queue-recovery.php';
require_once __DIR__ . '/delivery-journal.php';

function clasificarRecuperacion(
    string $estadoCola,
    ?string $estadoDiario
): string {
    if ($estadoCola === 'completado') {
        return $estadoDiario === 'aceptado'
            ? 'finalizado'
            : 'inconsistencia';
    }

    if ($estadoCola === 'pendiente') {
        return $estadoDiario === null
            ? 'pendiente_normal'
            : 'inconsistencia';
    }

    if ($estadoDiario === 'aceptado') {
        return 'revisar_finalizacion';
    }

    if ($estadoDiario === 'enviando' ||
        $estadoDiario === 'incierto') {
        return 'envio_incierto';
    }

    if ($estadoDiario === 'rechazado') {
        return 'revisar_rechazo';
    }

    if ($estadoDiario === 'preparado') {
        return 'revisar_preparacion';
    }

    if ($estadoDiario === null) {
        return 'revisar_sin_envio';
    }

    return 'inconsistencia';
}

function revisarRecuperacionCola(): array
{
    $archivos = glob(sotoQueueDirectory() . '/*.json');

    if ($archivos === false) {
        throw new RuntimeException(
            'No se pudo leer la cola'
        );
    }

    $resultado = [];

    foreach ($archivos as $archivo) {
        $referencia = substr(
            basename($archivo, '.json'),
            0,
            12
        );

        try {
            $raw = file_get_contents($archivo);

            if ($raw === false) {
                throw new RuntimeException('Lectura fallida');
            }

            $registro = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($registro)) {
                throw new RuntimeException(
                    'Registro inválido'
                );
            }

            $id = $registro['message_id'] ?? null;
            $estadoCola = $registro['estado'] ?? null;

            if (
                !is_string($id) ||
                !str_starts_with($id, 'wamid.') ||
                basename($archivo) !==
                    hash('sha256', $id) . '.json' ||
                !is_string($estadoCola)
            ) {
                throw new RuntimeException(
                    'Registro inconsistente'
                );
            }

            $journalFile = sotoJournalDir() . '/' .
                hash('sha256', $id) . '.json';

            $estadoDiario = null;

            if (is_file($journalFile)) {
                $journalRaw = file_get_contents($journalFile);

                if ($journalRaw === false) {
                    throw new RuntimeException(
                        'Diario ilegible'
                    );
                }

                $diario = json_decode(
                    $journalRaw,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if (!is_array($diario) ||
                    !is_string($diario['estado'] ?? null)) {
                    throw new RuntimeException(
                        'Diario inválido'
                    );
                }

                $estadoDiario = $diario['estado'];
            }

            $clasificacion = clasificarRecuperacion(
                $estadoCola,
                $estadoDiario
            );

            $resultado[] = [
                'ref' => $referencia,
                'cola' => $estadoCola,
                'diario' => $estadoDiario ?? 'sin_registro',
                'diagnostico' => $clasificacion
            ];

        } catch (Throwable $e) {
            // No intentar reparar registros dañados.
            $resultado[] = [
                'ref' => $referencia,
                'cola' => 'desconocido',
                'diario' => 'desconocido',
                'diagnostico' => 'registro_corrupto'
            ];
        }
    }

    return $resultado;
}

function mostrarRevisionRecuperacion(array $revision): void
{
    echo "\n=== REVISIÓN DE RECUPERACIÓN ===\n\n";

    $incidencias = 0;

    foreach ($revision as $item) {
        $diagnostico = $item['diagnostico'];

        if (!in_array(
            $diagnostico,
            ['finalizado', 'pendiente_normal'],
            true
        )) {
            $incidencias++;
        }

        echo '[' . $item['ref'] . '] '
            . 'Cola: ' . $item['cola']
            . ' | Diario: ' . $item['diario']
            . ' | Diagnóstico: ' . $diagnostico
            . "\n";
    }

    echo "\nMensajes examinados: "
        . count($revision) . "\n";

    echo "Incidencias para revisar: "
        . $incidencias . "\n";

    echo "\nNingún mensaje ha sido reenviado.\n";
}
