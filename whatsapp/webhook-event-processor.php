<?php
declare(strict_types=1);

/**
 * Entrada de eventos WhatsApp - Soto Digital.
 *
 * Recibe eventos YA autenticados.
 * No verifica firmas, no llama a Claude
 * y no envía mensajes.
 */

function sotoQueueDirectory(): string
{
    $configured = getenv('SOTO_QUEUE_DIR');

    // Ubicación exclusivamente para desarrollo.
    $dir = $configured ?: (
        sys_get_temp_dir() . '/soto-whatsapp-queue-dev'
    );

    if (!is_dir($dir)) {
        $previousUmask = umask(0077);

        try {
            if (
                !mkdir($dir, 0700, true) &&
                !is_dir($dir)
            ) {
                throw new RuntimeException(
                    'No se pudo crear la cola'
                );
            }
        } finally {
            umask($previousUmask);
        }
    }

    if (!is_writable($dir)) {
        throw new RuntimeException(
            'La cola no permite escritura'
        );
    }

    return $dir;
}

/**
 * Encola un mensaje una única vez por ID.
 *
 * Los mensajes confirmados permanecen en la cola
 * hasta que el futuro trabajador los procese.
 */
function sotoEncolarMensaje(
    string $telefono,
    string $messageId,
    string $texto,
    ?string $timestamp = null
): string {
    if (!preg_match('/^[0-9]{8,16}$/', $telefono)) {
        throw new InvalidArgumentException(
            'Teléfono no válido'
        );
    }

    if (
        !str_starts_with($messageId, 'wamid.') ||
        strlen($messageId) > 512
    ) {
        throw new InvalidArgumentException(
            'ID de mensaje no válido'
        );
    }

    $texto = trim($texto);

    if ($texto === '' || strlen($texto) > 4000) {
        throw new InvalidArgumentException(
            'Texto vacío o demasiado largo'
        );
    }

    $file = sotoQueueDirectory() . '/' .
        hash('sha256', $messageId) . '.json';

    $record = [
        'message_id' => $messageId,
        'telefono' => $telefono,
        'texto' => $texto,
        'timestamp_meta' => $timestamp,
        'recibido_en' => gmdate('c'),
        'estado' => 'pendiente',
        'intentos' => 0
    ];

    $json = json_encode(
        $record,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    $previousUmask = umask(0077);

    try {
        // Creación exclusiva: no sobrescribe un
        // mensaje que ya esté encolado.
        $handle = @fopen($file, 'x');
    } finally {
        umask($previousUmask);
    }

    if ($handle === false) {
        if (is_file($file)) {
            return 'duplicado';
        }

        throw new RuntimeException(
            'No se pudo crear el registro del mensaje'
        );
    }

    try {
        $length = strlen($json);
        $written = 0;

        while ($written < $length) {
            $n = fwrite(
                $handle,
                substr($json, $written)
            );

            if ($n === false || $n === 0) {
                throw new RuntimeException(
                    'Error escribiendo el mensaje'
                );
            }

            $written += $n;
        }

        if (!fflush($handle)) {
            throw new RuntimeException(
                'No se pudo vaciar el búfer'
            );
        }

    } catch (Throwable $e) {
        fclose($handle);
        @unlink($file);
        throw $e;
    }

    fclose($handle);

    return 'encolado';
}

/**
 * Procesa el contenido YA autenticado de Meta.
 *
 * Descarta estados de entrega y mensajes no textuales.
 */
function procesarEventoWebhook(array $payload): array
{
    $resultado = [
        'encolados' => 0,
        'duplicados' => 0,
        'ignorados' => 0
    ];

    if (($payload['object'] ?? null) !==
        'whatsapp_business_account') {
        throw new InvalidArgumentException(
            'Tipo de evento no reconocido'
        );
    }

    foreach (($payload['entry'] ?? []) as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        foreach (($entry['changes'] ?? []) as $change) {
            if (!is_array($change)) {
                continue;
            }

            if (($change['field'] ?? '') !== 'messages') {
                continue;
            }

            $value = $change['value'] ?? [];

            if (!is_array($value)) {
                continue;
            }

            // No procesamos notificaciones de entrega,
            // lectura u otros eventos como mensajes.
            $messages = $value['messages'] ?? [];

            if (!is_array($messages)) {
                continue;
            }

            foreach ($messages as $message) {
                if (!is_array($message)) {
                    $resultado['ignorados']++;
                    continue;
                }

                if (($message['type'] ?? '') !== 'text') {
                    $resultado['ignorados']++;
                    continue;
                }

                $telefono = $message['from'] ?? null;
                $messageId = $message['id'] ?? null;
                $texto = $message['text']['body'] ?? null;
                $timestamp = $message['timestamp'] ?? null;

                if (
                    !is_string($telefono) ||
                    !preg_match('/^[0-9]{8,16}$/', $telefono) ||
                    !is_string($messageId) ||
                    !str_starts_with($messageId, 'wamid.') ||
                    strlen($messageId) > 512 ||
                    !is_string($texto) ||
                    trim($texto) === '' ||
                    strlen($texto) > 4000
                ) {
                    $resultado['ignorados']++;
                    continue;
                }

                $estado = sotoEncolarMensaje(
                    $telefono,
                    $messageId,
                    $texto,
                    is_string($timestamp)
                        ? $timestamp
                        : null
                );

                if ($estado === 'duplicado') {
                    $resultado['duplicados']++;
                } else {
                    $resultado['encolados']++;
                }
            }
        }
    }

    return $resultado;
}
