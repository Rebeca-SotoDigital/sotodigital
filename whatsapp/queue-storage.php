<?php
declare(strict_types=1);

/**
 * Almacenamiento seguro de mensajes WhatsApp.
 *
 * Versión de desarrollo:
 * - Escribe primero en un archivo temporal.
 * - Utiliza un bloqueo por ID de mensaje.
 * - Comprueba los registros existentes.
 * - No sobrescribe mensajes de la cola.
 * - No contacta con Meta ni Claude.
 */

require_once __DIR__ . '/webhook-event-processor.php';

/**
 * Guarda un mensaje en la cola mediante
 * escritura temporal y publicación atómica.
 */
function sotoEncolarMensajeSeguro(
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
        strlen($messageId) > 512 ||
        strlen($messageId) < 8
    ) {
        throw new InvalidArgumentException(
            'Identificador no válido'
        );
    }

    $texto = trim($texto);

    if ($texto === '' || strlen($texto) > 4000) {
        throw new InvalidArgumentException(
            'Texto no válido'
        );
    }

    $directorio = sotoQueueDirectory();
    $hash = hash('sha256', $messageId);

    $archivo = $directorio . '/' . $hash . '.json';
    $archivoLock = $directorio . '/' . $hash . '.lock';

    $old = umask(0077);

    try {
        $lock = fopen($archivoLock, 'c');
    } finally {
        umask($old);
    }

    if ($lock === false) {
        throw new RuntimeException(
            'No se pudo abrir el bloqueo'
        );
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException(
                'No se pudo bloquear el mensaje'
            );
        }

        // Comprobar el registro antes de asumir
        // que es un mensaje duplicado.
        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);

            if ($contenido === false) {
                throw new RuntimeException(
                    'No se pudo leer el registro existente'
                );
            }

            try {
                $existente = json_decode(
                    $contenido,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (JsonException $e) {
                throw new RuntimeException(
                    'Registro existente corrupto'
                );
            }

            if (
                !is_array($existente) ||
                ($existente['message_id'] ?? null) !==
                    $messageId ||
                ($existente['telefono'] ?? null) !==
                    $telefono ||
                !in_array(
                    $existente['estado'] ?? null,
                    [
                        'pendiente',
                        'procesando',
                        'completado',
                        'revision'
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Registro existente inconsistente'
                );
            }

            return 'duplicado';
        }

        // Registro compatible con queue-worker.php.
        $registro = [
            'message_id' => $messageId,
            'telefono' => $telefono,
            'texto' => $texto,
            'timestamp_meta' => $timestamp,
            'recibido_en' => gmdate('c'),
            'estado' => 'pendiente',
            'intentos' => 0
        ];

        $json = json_encode(
            $registro,
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );

        // Temporal en el mismo directorio para
        // que rename() sea una operación atómica
        // en un sistema de archivos compatible.
        $temporal = tempnam(
            $directorio,
            '.soto-pending-'
        );

        if ($temporal === false) {
            throw new RuntimeException(
                'No se pudo crear archivo temporal'
            );
        }

        try {
            chmod($temporal, 0600);

            $handle = fopen($temporal, 'wb');

            if ($handle === false) {
                throw new RuntimeException(
                    'No se pudo abrir el temporal'
                );
            }

            try {
                $total = strlen($json);
                $escritos = 0;

                while ($escritos < $total) {
                    $n = fwrite(
                        $handle,
                        substr($json, $escritos)
                    );

                    if ($n === false || $n === 0) {
                        throw new RuntimeException(
                            'Escritura incompleta'
                        );
                    }

                    $escritos += $n;
                }

                if (!fflush($handle)) {
                    throw new RuntimeException(
                        'Error vaciando el búfer'
                    );
                }

                // Sincronización cuando esté disponible.
                if (
                    function_exists('fsync') &&
                    !fsync($handle)
                ) {
                    throw new RuntimeException(
                        'Error sincronizando archivo'
                    );
                }

            } finally {
                fclose($handle);
            }

            // Comprobación previa a la publicación.
            $verificacion = file_get_contents($temporal);

            if ($verificacion !== $json) {
                throw new RuntimeException(
                    'Verificación de escritura fallida'
                );
            }

            if (!rename($temporal, $archivo)) {
                throw new RuntimeException(
                    'No se pudo publicar el mensaje'
                );
            }

        } finally {
            if (is_file($temporal)) {
                unlink($temporal);
            }
        }

        return 'encolado';

    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
